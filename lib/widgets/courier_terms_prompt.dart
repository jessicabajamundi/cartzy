import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

class CourierTermsPrompt extends StatelessWidget {
  final bool agreed;
  final bool showError;
  final ValueChanged<bool> onChanged;
  const CourierTermsPrompt(
      {super.key,
      required this.agreed,
      required this.onChanged,
      this.showError = false});

  Future<void> _open(BuildContext context) async {
    Map<String, dynamic> policy;
    try {
      policy = jsonDecode(
              await rootBundle.loadString('assets/legal/courier-terms.json'))
          as Map<String, dynamic>;
    } catch (_) {
      if (context.mounted)
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('Could not open courier terms. Please try again.')));
      return;
    }
    if (!context.mounted) return;
    var checked = agreed;
    await showDialog<void>(
        context: context,
        builder: (dialogContext) => StatefulBuilder(
              builder: (context, update) => Dialog(
                insetPadding: const EdgeInsets.all(16),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(20)),
                backgroundColor:
                    CartzyColors.surfaceOf(context, light: Colors.white),
                child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 620),
                    child: SizedBox(
                      height: MediaQuery.sizeOf(context).height * .84,
                      child: Column(children: [
                        Padding(
                            padding: const EdgeInsets.fromLTRB(20, 12, 8, 12),
                            child: Row(children: [
                              const Icon(Icons.description_outlined, size: 20),
                              const SizedBox(width: 10),
                              const Expanded(
                                  child: Text('Courier Terms & Privacy Policy',
                                      style: TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.bold))),
                              IconButton(
                                  tooltip: 'Close modal',
                                  onPressed: () =>
                                      Navigator.of(dialogContext).pop(),
                                  icon: const Icon(Icons.close, size: 18)),
                            ])),
                        const Divider(height: 1),
                        Expanded(
                            child: SingleChildScrollView(
                                padding:
                                    const EdgeInsets.fromLTRB(24, 20, 24, 24),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Center(
                                        child: Text('CARTZY',
                                            style: TextStyle(
                                                fontWeight: FontWeight.w800,
                                                fontSize: 17))),
                                    const Center(
                                        child: Text(
                                            'TERMS & CONDITIONS AND PRIVACY POLICY',
                                            textAlign: TextAlign.center,
                                            style: TextStyle(
                                                fontWeight: FontWeight.w700,
                                                fontSize: 12))),
                                    const SizedBox(height: 16),
                                    for (final block
                                        in (policy['blocks'] as List)
                                            .cast<Map<String, dynamic>>())
                                      _block(context, block),
                                    const Divider(height: 32),
                                    CheckboxListTile(
                                      contentPadding: EdgeInsets.zero,
                                      controlAffinity:
                                          ListTileControlAffinity.leading,
                                      activeColor: const Color(0xFF6F6382),
                                      value: checked,
                                      title: Text(policy['agreement'] as String,
                                          style: const TextStyle(
                                              fontSize: 12.5, height: 1.6)),
                                      onChanged: (value) {
                                        update(() => checked = value ?? false);
                                        onChanged(checked);
                                      },
                                    ),
                                    const SizedBox(height: 14),
                                    Align(
                                        alignment: Alignment.centerRight,
                                        child: DecoratedBox(
                                          decoration: BoxDecoration(
                                              borderRadius:
                                                  BorderRadius.circular(10),
                                              gradient: const LinearGradient(
                                                  colors: [
                                                    Color(0xFF6F6382),
                                                    Color(0xFF8C5A50)
                                                  ])),
                                          child: TextButton(
                                            style: TextButton.styleFrom(
                                                foregroundColor: Colors.white,
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                        horizontal: 24,
                                                        vertical: 12)),
                                            onPressed: () {
                                              onChanged(true);
                                              Navigator.of(dialogContext).pop();
                                            },
                                            child: const Text('I Agree',
                                                style: TextStyle(
                                                    fontWeight:
                                                        FontWeight.bold)),
                                          ),
                                        )),
                                  ],
                                ))),
                      ]),
                    )),
              ),
            ));
  }

  Widget _block(BuildContext context, Map<String, dynamic> block) {
    final type = block['type'];
    final style = TextStyle(
        fontSize: 12.5,
        height: 1.6,
        color: CartzyColors.textOf(context, light: const Color(0xFF4B5563)));
    if (type == 'list') {
      return Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: Column(children: [
            for (final item in block['items'] as List)
              Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('•  ', style: style),
                      Expanded(child: Text('$item', style: style))
                    ],
                  )),
          ]));
    }
    if (type == 'banner') {
      return Container(
        width: double.infinity,
        margin: const EdgeInsets.only(top: 14, bottom: 10),
        padding: const EdgeInsets.only(bottom: 5),
        decoration: BoxDecoration(
            border: Border(
                bottom: BorderSide(
                    color: CartzyColors.borderOf(context,
                        light: const Color(0xFFECE7F2))))),
        child: Text('${block['text']}',
            style: style.copyWith(
                fontWeight: FontWeight.w800,
                color: CartzyColors.mutedOf(context,
                    light: const Color(0xFF6F6382)))),
      );
    }
    return Padding(
        padding: EdgeInsets.only(
            top: type == 'heading' ? 12 : 0, bottom: type == 'heading' ? 4 : 8),
        child: Text('${block['text']}',
            style: style.copyWith(
                fontWeight:
                    type == 'heading' ? FontWeight.w700 : FontWeight.normal)));
  }

  @override
  Widget build(BuildContext context) =>
      Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const Text('Read and agree to the', style: TextStyle(fontSize: 13)),
        TextButton(
            onPressed: () => _open(context),
            style: TextButton.styleFrom(
                padding: EdgeInsets.zero, alignment: Alignment.centerLeft),
            child: const Text(
                'Cartzy Courier Terms & Conditions and Privacy Policy',
                style: TextStyle(
                    decoration: TextDecoration.underline,
                    fontWeight: FontWeight.bold))),
        if (agreed)
          const Row(children: [
            Icon(Icons.check_circle, size: 15, color: Color(0xFF15803D)),
            SizedBox(width: 5),
            Text('Agreed',
                style: TextStyle(
                    color: Color(0xFF15803D), fontWeight: FontWeight.bold))
          ]),
        if (showError && !agreed)
          const Text(
              'You must open and agree to the courier terms and privacy policy to proceed.',
              style: TextStyle(color: Color(0xFFBE123C), fontSize: 12.5)),
      ]);
}
