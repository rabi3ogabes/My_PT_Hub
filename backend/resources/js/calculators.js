// Client-side calculators for the public marketing site. Each is an Alpine component;
// explanations and formulas are server-rendered for SEO, these only compute.

const round = (n, d = 0) => {
    const f = Math.pow(10, d)
    return Math.round((Number(n) || 0) * f) / f
}

const LB = 0.45359237
const IN = 2.54

function units() {
    return {
        unit: 'metric',
        kg(v) {
            return this.unit === 'metric' ? Number(v) : Number(v) * LB
        },
        cm(v) {
            return this.unit === 'metric' ? Number(v) : Number(v) * IN
        },
    }
}

export const ACTIVITY = { sedentary: 1.2, light: 1.375, moderate: 1.55, active: 1.725, athlete: 1.9 }

export function bmr({ sex, weightKg, heightCm, age }) {
    return 10 * weightKg + 6.25 * heightCm - 5 * age + (sex === 'female' ? -161 : 5)
}

export function tdee() {
    return {
        ...units(),
        sex: 'male',
        age: 30,
        weight: 75,
        height: 178,
        activity: 'moderate',
        get bmr() {
            return round(bmr({ sex: this.sex, weightKg: this.kg(this.weight), heightCm: this.cm(this.height), age: Number(this.age) }))
        },
        get tdee() {
            return round(this.bmr * (ACTIVITY[this.activity] ?? 1.55))
        },
        get cut() {
            return round(this.tdee * 0.8)
        },
        get bulk() {
            return round(this.tdee * 1.1)
        },
    }
}

export function macros() {
    return {
        calories: 2400,
        weight: 75,
        goal: 'maintain',
        proteinPerKg: 2,
        fatShare: 25,
        get target() {
            const adj = { cut: 0.8, maintain: 1, bulk: 1.1 }[this.goal] ?? 1
            return round(Number(this.calories) * adj)
        },
        get protein() {
            return round(Number(this.weight) * Number(this.proteinPerKg))
        },
        get fat() {
            return round((this.target * (Number(this.fatShare) / 100)) / 9)
        },
        get carbs() {
            return Math.max(0, round((this.target - this.protein * 4 - this.fat * 9) / 4))
        },
        share(g, kcalPerGram) {
            return this.target > 0 ? round(((g * kcalPerGram) / this.target) * 100) : 0
        },
    }
}

export function oneRepMax() {
    return {
        weight: 100,
        reps: 5,
        get epley() {
            return Number(this.reps) <= 1 ? Number(this.weight) : round(Number(this.weight) * (1 + Number(this.reps) / 30), 1)
        },
        get brzycki() {
            const r = Math.min(Number(this.reps), 36)
            return r <= 1 ? Number(this.weight) : round((Number(this.weight) * 36) / (37 - r), 1)
        },
        get estimate() {
            return round((this.epley + this.brzycki) / 2, 1)
        },
        get table() {
            return [100, 95, 90, 85, 80, 75, 70, 65, 60].map((p) => ({ pct: p, load: round((this.estimate * p) / 100, 1) }))
        },
    }
}

export function bodyFat() {
    return {
        ...units(),
        sex: 'male',
        height: 178,
        neck: 38,
        waist: 84,
        hip: 96,
        get result() {
            const h = this.cm(this.height)
            const n = this.cm(this.neck)
            const w = this.cm(this.waist)
            const hp = this.cm(this.hip)
            let v
            if (this.sex === 'female') {
                if (w + hp - n <= 0) return 0
                v = 495 / (1.29579 - 0.35004 * Math.log10(w + hp - n) + 0.221 * Math.log10(h)) - 450
            } else {
                if (w - n <= 0) return 0
                v = 495 / (1.0324 - 0.19077 * Math.log10(w - n) + 0.15456 * Math.log10(h)) - 450
            }
            return Math.max(0, round(v, 1))
        },
        get band() {
            const r = this.result
            const t = this.sex === 'female' ? [14, 21, 25, 32] : [6, 14, 18, 25]
            if (r < t[0]) return 'essential'
            if (r < t[1]) return 'athletic'
            if (r < t[2]) return 'fitness'
            if (r < t[3]) return 'average'
            return 'high'
        },
    }
}

export function bmi() {
    return {
        ...units(),
        weight: 75,
        height: 178,
        get value() {
            const m = this.cm(this.height) / 100
            return m > 0 ? round(this.kg(this.weight) / (m * m), 1) : 0
        },
        get band() {
            const v = this.value
            if (v < 18.5) return 'under'
            if (v < 25) return 'healthy'
            if (v < 30) return 'over'
            return 'obese'
        },
        get position() {
            return Math.min(100, Math.max(0, ((this.value - 15) / 25) * 100))
        },
    }
}

export function protein() {
    return {
        weight: 75,
        goal: 'muscle',
        get range() {
            const r = { health: [1.0, 1.2], fatloss: [1.8, 2.4], muscle: [1.6, 2.2], endurance: [1.4, 1.8], older: [1.2, 1.6] }[this.goal] ?? [1.6, 2.2]
            return [round(Number(this.weight) * r[0]), round(Number(this.weight) * r[1])]
        },
        get perMeal() {
            return round(((this.range[0] + this.range[1]) / 2) / 4)
        },
    }
}

export function water() {
    return {
        weight: 75,
        training: 60,
        hot: false,
        get litres() {
            const base = Number(this.weight) * 0.035
            const training = (Number(this.training) / 60) * 0.5
            return round(base + training + (this.hot ? 0.5 : 0), 1)
        },
        get glasses() {
            return Math.ceil((this.litres * 1000) / 250)
        },
    }
}

export function ptPricing() {
    return {
        income: 60000,
        expenses: 12000,
        weeks: 46,
        sessions: 22,
        tax: 20,
        get perSession() {
            const sessionsYear = Number(this.weeks) * Number(this.sessions)
            if (sessionsYear <= 0) return 0
            const gross = (Number(this.income) / (1 - Math.min(Number(this.tax), 90) / 100)) + Number(this.expenses)
            return round(gross / sessionsYear)
        },
        get pack10() {
            return round(this.perSession * 10 * 0.9)
        },
        get monthlyOnline() {
            return round(this.perSession * 2.2)
        },
    }
}

export function coachEarnings() {
    return {
        clients: 25,
        price: 150,
        fees: 3,
        software: 39,
        churn: 6,
        get monthly() {
            const gross = Number(this.clients) * Number(this.price)
            return round(gross * (1 - Number(this.fees) / 100) - Number(this.software))
        },
        get yearly() {
            return round(this.monthly * 12)
        },
        get lifetime() {
            const churn = Math.max(Number(this.churn), 0.5) / 100
            return round((Number(this.price) * (1 - Number(this.fees) / 100)) / churn)
        },
    }
}

export const calculators = { tdee, macros, oneRepMax, bodyFat, bmi, protein, water, ptPricing, coachEarnings }
