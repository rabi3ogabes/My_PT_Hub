import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../shared/l10n_ext.dart';
import 'progress_screen.dart';

/// Before/after slider for progress photos of the same pose.
class PhotoCompareScreen extends ConsumerStatefulWidget {
  const PhotoCompareScreen({super.key});

  @override
  ConsumerState<PhotoCompareScreen> createState() => _PhotoCompareScreenState();
}

class _PhotoCompareScreenState extends ConsumerState<PhotoCompareScreen> {
  String _pose = 'front';
  ProgressPhoto? _before;
  ProgressPhoto? _after;
  double _split = 0.5;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final progress = ref.watch(progressProvider);
    final photos = (progress.value?.value.photos ?? const <ProgressPhoto>[]).where((p) => p.pose == _pose).toList()
      ..sort((a, b) => a.takenOn.compareTo(b.takenOn));
    final before = _before != null && photos.contains(_before) ? _before! : photos.firstOrNull;
    final after = _after != null && photos.contains(_after) ? _after! : photos.lastOrNull;

    return Scaffold(
      appBar: AppBar(title: Text(l10n.compare)),
      body: ListView(
        padding: const EdgeInsetsDirectional.all(AuSpacing.s5),
        children: [
          AuSegmented<String>(
            segments: {'front': l10n.poseFront, 'side': l10n.poseSide, 'back': l10n.poseBack},
            selected: _pose,
            onChanged: (p) => setState(() {
              _pose = p;
              _before = null;
              _after = null;
            }),
          ),
          const SizedBox(height: AuSpacing.s5),
          if (photos.length < 2)
            AuEmptyState(icon: Icons.compare_rounded, title: l10n.notEnoughPhotos, message: l10n.notEnoughPhotosHint, compact: true)
          else ...[
            AspectRatio(
              aspectRatio: 3 / 4,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(AuRadius.lg),
                child: LayoutBuilder(
                  builder: (context, c) {
                    // The slider is a visual comparison; keep it LTR so
                    // "before" is always on the left like a timeline.
                    return Directionality(
                      textDirection: TextDirection.ltr,
                      child: GestureDetector(
                        onHorizontalDragUpdate: (d) => setState(() => _split = (d.localPosition.dx / c.maxWidth).clamp(0.02, 0.98)),
                        onTapDown: (d) => setState(() => _split = (d.localPosition.dx / c.maxWidth).clamp(0.02, 0.98)),
                        child: Stack(
                          fit: StackFit.expand,
                          children: [
                            _Photo(url: after!.url),
                            ClipRect(
                              clipper: _SplitClipper(_split),
                              child: _Photo(url: before!.url),
                            ),
                            Positioned(
                              left: c.maxWidth * _split - 1,
                              top: 0,
                              bottom: 0,
                              child: Container(width: 2, color: au.accent),
                            ),
                            Positioned(
                              left: c.maxWidth * _split - 22,
                              top: c.maxHeight / 2 - 22,
                              child: Container(
                                width: 44,
                                height: 44,
                                decoration: BoxDecoration(shape: BoxShape.circle, gradient: au.goldGradient, boxShadow: au.glow),
                                child: Icon(Icons.unfold_more_rounded, color: au.onAccent),
                              ),
                            ),
                            Positioned(left: 12, top: 12, child: AuBadge(label: context.formatDate(parseApiDate(before.takenOn), pattern: 'yMMMd'), tone: AuBadgeTone.neutral)),
                            Positioned(right: 12, top: 12, child: AuBadge(label: context.formatDate(parseApiDate(after.takenOn), pattern: 'yMMMd'))),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
            ),
            const SizedBox(height: AuSpacing.s5),
            _DatePicker(label: l10n.before, photos: photos, selected: before, onSelected: (p) => setState(() => _before = p)),
            const SizedBox(height: AuSpacing.s3),
            _DatePicker(label: l10n.after, photos: photos, selected: after, onSelected: (p) => setState(() => _after = p)),
          ],
        ],
      ),
    );
  }
}

class _SplitClipper extends CustomClipper<Rect> {
  _SplitClipper(this.split);

  final double split;

  @override
  Rect getClip(Size size) => Rect.fromLTWH(0, 0, size.width * split, size.height);

  @override
  bool shouldReclip(_SplitClipper old) => old.split != split;
}

class _Photo extends StatelessWidget {
  const _Photo({required this.url});

  final String? url;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    if (url == null) return ColoredBox(color: au.surfaceSunken);
    return Image.network(url!, fit: BoxFit.cover, errorBuilder: (_, _, _) => ColoredBox(color: au.surfaceSunken));
  }
}

class _DatePicker extends StatelessWidget {
  const _DatePicker({required this.label, required this.photos, required this.selected, required this.onSelected});

  final String label;
  final List<ProgressPhoto> photos;
  final ProgressPhoto? selected;
  final ValueChanged<ProgressPhoto> onSelected;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    return Row(
      children: [
        SizedBox(width: 72, child: Text(label, style: au.text.label.copyWith(color: au.textSecondary))),
        Expanded(
          child: SizedBox(
            height: 44,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: photos.length,
              separatorBuilder: (_, _) => const SizedBox(width: AuSpacing.s2),
              itemBuilder: (_, i) => AuChip(
                label: context.formatDate(parseApiDate(photos[i].takenOn), pattern: 'd MMM yy'),
                selected: photos[i] == selected,
                onSelected: (_) => onSelected(photos[i]),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
