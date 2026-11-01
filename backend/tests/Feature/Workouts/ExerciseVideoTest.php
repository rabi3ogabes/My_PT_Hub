<?php

use App\Domain\Workouts\Jobs\ProcessExerciseVideo;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Services\ExerciseLibrary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Model::preventLazyLoading(); // catch N+1 / lazy loads (dev behaviour)
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    Storage::fake('public');
    $this->actingAsIn($this->owner, $this->tenant);
    $this->exerciseId = $this->postJson('/api/v1/exercises', ['name' => 'Cable Fly'])->assertCreated()->json('data.id');
});

it('stores an uploaded video as pending and queues processing', function () {
    Queue::fake();
    $file = UploadedFile::fake()->create('fly.mp4', 512, 'video/mp4');

    $this->postJson("/api/v1/exercises/{$this->exerciseId}/video", ['video' => $file])->assertOk()
        ->assertJsonPath('data.video.status', 'pending')
        ->assertJsonPath('data.video.provider', 'upload');

    Queue::assertPushed(ProcessExerciseVideo::class, fn ($job) => $job->exerciseId === $this->exerciseId);
    Storage::disk('public')->assertExists(Exercise::withoutGlobalScopes()->find($this->exerciseId)->video_path);
});

it('stores YouTube and Vimeo links as embeds', function () {
    $this->postJson("/api/v1/exercises/{$this->exerciseId}/video", ['url' => 'https://vimeo.com/76979871'])->assertOk()
        ->assertJsonPath('data.video.provider', 'vimeo')
        ->assertJsonPath('data.video.url', 'https://player.vimeo.com/video/76979871');
    expectApiError($this->postJson("/api/v1/exercises/{$this->exerciseId}/video", ['url' => 'https://example.com/x.mp4']), 422, 'validation_failed');

    expect(ExerciseLibrary::parseEmbed('https://youtu.be/dQw4w9WgXcQ')['url'])->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and(ExerciseLibrary::parseEmbed('https://www.youtube.com/shorts/dQw4w9WgXcQ')['provider'])->toBe('youtube');
});

it('publishes the original upload when FFmpeg is unavailable', function () {
    config(['workouts.ffmpeg_binary' => '/nonexistent/ffmpeg']);
    Queue::fake();
    $this->postJson("/api/v1/exercises/{$this->exerciseId}/video", ['video' => UploadedFile::fake()->create('fly.mov', 100, 'video/quicktime')])->assertOk();

    (new ProcessExerciseVideo($this->exerciseId))->handle();
    $exercise = Exercise::withoutGlobalScopes()->find($this->exerciseId);
    expect($exercise->video_provider)->toBe('upload')->and($exercise->video_status)->toBe('ready')->and($exercise->video_url)->toContain('exercise-videos/');
});

it('transcodes a real fixture to HLS renditions, poster and preview', function () {
    $ffmpeg = ProcessExerciseVideo::ffmpegBinary();
    if ($ffmpeg === null) {
        $this->markTestSkipped('ffmpeg is not installed');
    }
    $fixture = sys_get_temp_dir().'/aurum-fixture-'.uniqid().'.mp4';
    Process::run([$ffmpeg, '-y', '-f', 'lavfi', '-i', 'testsrc=duration=2:size=320x240:rate=24', '-f', 'lavfi', '-i', 'sine=frequency=440:duration=2', '-shortest', '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-c:a', 'aac', $fixture])->throw();
    config(['workouts.hls_renditions' => [240 => 300, 480 => 600]]);
    Queue::fake();

    $this->postJson("/api/v1/exercises/{$this->exerciseId}/video", ['video' => new UploadedFile($fixture, 'demo.mp4', 'video/mp4', null, true)])->assertOk();
    (new ProcessExerciseVideo($this->exerciseId))->handle();
    @unlink($fixture);

    $exercise = Exercise::withoutGlobalScopes()->find($this->exerciseId);
    expect($exercise->video_status)->toBe('ready')
        ->and($exercise->video_provider)->toBe('hls')
        ->and($exercise->video_url)->toEndWith('master.m3u8')
        ->and($exercise->poster_url)->toEndWith('poster.jpg')
        ->and($exercise->video_size_bytes)->toBeGreaterThan(0);

    $dir = dirname((string) parse_url($exercise->video_url, PHP_URL_PATH));
    $dir = substr($dir, strpos($dir, 'exercise-videos/'));
    $master = Storage::disk('public')->get($dir.'/master.m3u8');
    expect($master)->toContain('240p/index.m3u8')->toContain('480p/index.m3u8');
    Storage::disk('public')->assertExists($dir.'/480p/index.m3u8');
});

afterEach(fn () => Model::preventLazyLoading(false));
