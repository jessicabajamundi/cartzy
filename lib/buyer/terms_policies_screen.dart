import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';

// ──────────────────────────────────────────────────────────────
// PALETTE (lavender + rose gold, same as the cart and menu)
// ──────────────────────────────────────────────────────────────
class _Palette {
  static const lavender = CartzyColors.brandDeep;
  static const roseGold = Color(0xFFB76E79);
  static const ink = CartzyColors.brand900;
  static const body = Color(0xFF4F4658);
  static const line = Color(0xFFEDE6EE);

  static const blend = LinearGradient(
    colors: [lavender, roseGold],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
}

// ──────────────────────────────────────────────────────────────
// CONTENT MODEL
// ──────────────────────────────────────────────────────────────
abstract class _Block {
  const _Block();
}

class _Para extends _Block {
  final String text;
  const _Para(this.text);
}

/// One bullet. [lead] is shown in bold before the text, e.g. "Damaged Item:".
class _Item {
  final String? lead;
  final String text;
  const _Item(this.text, {this.lead});
}

class _Bullets extends _Block {
  /// Each entry is a plain String or an [_Item] with a bold lead.
  final List<Object> items;
  const _Bullets(this.items);
}

class _Steps extends _Block {
  final List<String> steps;
  const _Steps(this.steps);
}

class _Table extends _Block {
  final List<String> header;
  final List<List<String>> rows;
  const _Table(this.header, this.rows);
}

class _Section {
  final String title;
  final List<_Block> blocks;
  const _Section(this.title, this.blocks);
}

class _Policy {
  final String tab;
  final String title;
  final IconData icon;
  final String? intro;
  final List<_Section> sections;
  const _Policy({
    required this.tab,
    required this.title,
    required this.icon,
    required this.sections,
    this.intro,
  });
}

// ──────────────────────────────────────────────────────────────
// POLICIES (full text of the Cartzy policy documents)
// ──────────────────────────────────────────────────────────────
const List<_Policy> _policies = [
  // ── TERMS OF SERVICE ──────────────────────────────────────────
  _Policy(
    tab: 'Terms of Service',
    title: 'Terms of Service',
    icon: Icons.gavel_rounded,
    intro:
        'Welcome to Cartzy! These Terms & Conditions explain the rules, responsibilities, and policies that apply when you use Cartzy as a buyer. By creating an account, browsing products, or placing an order, you agree to follow these terms.',
    sections: [
      _Section('1.1 Account Registration', [
        _Bullets([
          'Buyers must provide accurate, complete, and updated personal information when creating an account.',
          'Each buyer is responsible for keeping their account password and login details secure.',
          "Buyers must not create fake accounts or use another person's identity without permission.",
          'Cartzy reserves the right to restrict or suspend accounts involved in fraudulent or suspicious activities.',
        ]),
      ]),
      _Section('1.2 Buyer Responsibilities', [
        _Bullets([
          'Buyers must carefully review product descriptions, images, prices, variations, quantities, and seller information before placing an order.',
          'Buyers must provide a complete and correct delivery address and active contact number.',
          'Buyers must ensure that someone is available to receive the parcel.',
          'Buyers must communicate respectfully with sellers, riders, couriers, and Cartzy customer support.',
          'Buyers must not misuse vouchers, discounts, promotions, or other platform benefits.',
        ]),
      ]),
      _Section('1.3 Orders and Payments', [
        _Bullets([
          'All orders are subject to product availability and seller confirmation.',
          'Buyers must pay the correct amount using the payment methods available on Cartzy.',
          'Buyers must not submit false payment confirmations or use unauthorized payment methods.',
          'Once an order has been confirmed or processed, cancellation may be subject to the applicable order and cancellation rules.',
          'Cartzy is not responsible for incorrect orders caused by information entered by the buyer.',
        ]),
      ]),
      _Section('1.4 Order Cancellation', [
        _Bullets([
          "Buyers may request cancellation before the seller processes or ships the order, subject to the platform's cancellation rules.",
          'Once an order has been shipped, cancellation may no longer be available.',
          'Repeated cancellations, refusal to receive parcels, or misuse of Cash on Delivery (COD) may result in account restrictions.',
          'Refunds for eligible cancelled orders will be processed according to the applicable refund procedure.',
        ]),
      ]),
      _Section('1.5 Shipping and Delivery', [
        _Bullets([
          "Delivery time may vary depending on the seller, buyer's location, logistics provider, weather, and other circumstances.",
          'Buyers are responsible for providing accurate delivery information.',
          'Buyers should monitor their order status through the Cartzy application or website.',
          'If a parcel is delayed, missing, damaged, or incorrectly delivered, the buyer must report the issue through Cartzy customer support.',
          'Buyers must not provide false delivery complaints or intentionally refuse valid orders.',
        ]),
      ]),
      _Section('1.6 Product Information', [
        _Bullets([
          'Buyers should review the product details and specifications before purchasing.',
          'Product colors and appearance may slightly differ due to lighting, screen settings, or photography.',
          'Buyers should contact the seller for clarification before completing a purchase.',
          'Cartzy may investigate product listings reported as misleading, fraudulent, or inaccurate.',
        ]),
      ]),
    ],
  ),

  // ── PRIVACY POLICY ───────────────────────────────────────────
  _Policy(
    tab: 'Privacy Policy',
    title: 'Privacy Policy',
    icon: Icons.shield_outlined,
    intro:
        'Cartzy respects the privacy of its buyers and is committed to protecting personal information in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173).',
    sections: [
      _Section('1.1 Information We Collect', [
        _Bullets([
          'Full name; email address; mobile or contact number; delivery and billing address.',
          'Account login information; order and purchase history; payment transaction details.',
          'Customer service conversations; device and application usage information.',
        ]),
      ]),
      _Section('1.2 How We Use Your Information', [
        _Bullets([
          'Create and manage buyer accounts; process orders and payments; arrange shipping and delivery.',
          'Provide order confirmations and delivery updates; respond to questions, complaints, and refund requests.',
          'Verify transactions and prevent fraudulent activities; improve website and mobile application services.',
          'Send promotional messages and special offers, subject to applicable privacy requirements and your preferences.',
        ]),
      ]),
      _Section('1.3 Sharing of Personal Information', [
        _Para(
            'Cartzy may share necessary buyer information with sellers to process and fulfill orders; riders and logistics or courier partners to complete deliveries; authorized payment providers to process transactions; customer support personnel to resolve concerns; and government authorities when disclosure is required by law. Cartzy will only share information necessary for the relevant purpose and will apply appropriate safeguards.'),
      ]),
      _Section('1.4 Data Protection and Security', [
        _Bullets([
          'Cartzy will implement reasonable security measures to protect buyer information.',
          'Buyers must keep their account credentials confidential.',
          'Cartzy will take appropriate steps to prevent unauthorized access, disclosure, alteration, or misuse of personal information.',
        ]),
      ]),
      _Section('1.5 Data Retention', [
        _Para(
            'Cartzy will retain personal information only for as long as necessary to fulfill business, transaction, security, and legal requirements.'),
      ]),
      _Section("1.6 Buyer's Privacy Rights", [
        _Bullets([
          'Request access to personal information and correction of inaccurate information.',
          'Object to certain types of processing and request deletion or blocking when legally allowed.',
          'Withdraw consent where processing is based on consent.',
          'File a complaint regarding the improper handling of personal information.',
        ]),
        _Para(
            'Privacy-related requests may be submitted through Cartzy customer support.'),
      ]),
    ],
  ),

  // ── RETURN AND REFUND POLICY ─────────────────────────────────
  _Policy(
    tab: 'Return & Refund',
    title: 'Return and Refund Policy',
    icon: Icons.assignment_return_outlined,
    intro:
        'Cartzy aims to provide buyers with a fair and convenient return and refund process. Buyers may request a return or refund if their order meets the conditions stated below.',
    sections: [
      _Section('1.1 Return and Refund Period', [
        _Para(
            'Buyers must submit return or refund requests within the following proposed periods, counted from the actual delivery date and time:'),
        _Table(
          ['Seller Category', 'Return/Refund Period'],
          [
            ['Regular Seller', '7 calendar days'],
            ['Verified Official Store / Mall Seller', '15 calendar days'],
          ],
        ),
        _Para(
            "The periods are proposed reference windows inspired by return windows used by major Philippine e-commerce platforms. Cartzy's final implementation must follow applicable consumer protection laws."),
      ]),
      _Section('1.2 Valid Reasons for Return or Refund', [
        _Bullets([
          _Item('The delivered product is different from the product ordered.',
              lead: 'Wrong Item Received:'),
          _Item('The product arrived broken, damaged, or physically defective.',
              lead: 'Damaged Item:'),
          _Item('The product does not function as intended.',
              lead: 'Defective or Faulty Product:'),
          _Item(
              'The parcel contains incomplete products, missing accessories, or incorrect quantities.',
              lead: 'Missing Items or Parts:'),
          _Item(
              "The received product does not match the seller's description, specifications, or advertised features.",
              lead: 'Item Not as Described:'),
          _Item('The buyer received an expired item.',
              lead: 'Expired Product:'),
          _Item(
              'The order is marked as delivered but was not actually received by the buyer.',
              lead: 'Parcel Not Received:'),
          _Item('The buyer received a package without the purchased product.',
              lead: 'Empty Parcel:'),
          _Item(
              "The buyer wishes to return an item due to a change of mind, provided the item and category are eligible under Cartzy's return rules.",
              lead: 'Eligible Change of Mind:'),
        ]),
      ]),
      _Section('1.3 Conditions for Returning Products', [
        _Bullets([
          'Submit the request within the applicable return period.',
          'Provide clear photos or videos showing the issue.',
          'Include original packaging, tags, accessories, manuals, and free items, when applicable.',
          'Ensure the product is properly packed before returning it.',
          'Follow the return instructions provided by Cartzy.',
          'For change-of-mind returns, products must generally be unused, undamaged, complete, and in their original packaging.',
        ]),
        _Para(
            "These conditions do not remove the buyer's legal rights concerning defective, unsafe, or misrepresented products."),
      ]),
      _Section('1.4 Non-Returnable Products', [
        _Bullets([
          'Personal hygiene and intimate products.',
          'Used cosmetics and personal care products.',
          'Perishable food and grocery products.',
          'Digital products and downloadable services.',
          'Products with broken safety seals or opened packaging, where return is not permitted.',
          'Customized or personalized products.',
          'Other products identified as non-returnable before purchase.',
        ]),
        _Para(
            'This restriction does not prevent buyers from seeking remedies for defective, damaged, incorrect, or misrepresented products when required by law.'),
      ]),
      _Section('1.5 Return and Refund Procedure', [
        _Steps([
          'Open the Cartzy website or mobile application.',
          'Go to My Orders.',
          'Select the order containing the affected product.',
          'Click Return/Refund Request.',
          'Choose the appropriate reason.',
          'Upload supporting photos or videos.',
          "Submit the request and wait for Cartzy's review.",
          'Follow the return instructions if the product needs to be sent back.',
        ]),
        _Para(
            'Cartzy may request additional information or evidence before approving or rejecting a request.'),
      ]),
      _Section('1.6 Refund Processing', [
        _Bullets([
          'Refunds will only be processed after the request has been reviewed and approved.',
          'Depending on the case, the buyer may be required to return the product before receiving a refund.',
          'Approved refunds will be returned through the original payment method or another available refund method.',
          'Processing time may vary depending on the payment provider and banking institution.',
          'Shipping fees will be refunded when required by the applicable policy or law.',
        ]),
      ]),
      _Section('1.7 Return Shipping Fees', [
        _Bullets([
          'If the return is caused by a wrong, damaged, defective, or incomplete product, the seller or responsible party shall shoulder the applicable return shipping cost.',
          "For eligible change-of-mind returns, the buyer may be responsible for the return shipping fee, subject to Cartzy's rules.",
          'Buyers must follow the approved return shipping instructions.',
        ]),
      ]),
      _Section('1.8 Rejected Return or Refund Requests', [
        _Bullets([
          'The request is submitted beyond the applicable return period, except where another legal remedy applies.',
          'The buyer provides false or misleading evidence.',
          'The returned product does not match the reported issue.',
          'The buyer fails to return the item within the required period without a valid reason.',
          'The request does not meet the applicable return conditions.',
        ]),
        _Para(
            'Buyers may contact Cartzy customer support if they wish to dispute a decision.'),
      ]),
    ],
  ),

  // ── PROHIBITED ITEMS AND ACTIVITIES ──────────────────────────
  _Policy(
    tab: 'Prohibited Items',
    title: 'Prohibited Items and Activities',
    icon: Icons.block_rounded,
    intro:
        'Buyers must not use Cartzy to purchase, sell, promote, or facilitate transactions involving prohibited or illegal products.',
    sections: [
      _Section('1.1 Prohibited Products', [
        _Bullets([
          'Illegal drugs and controlled substances.',
          'Firearms, ammunition, and unauthorized weapons.',
          'Explosives and hazardous materials.',
          'Counterfeit, fake, or stolen products.',
          'Products made from protected wildlife or endangered species.',
          'Illegal medicines and unauthorized prescription drugs.',
          'Products that violate intellectual property rights.',
          'Other goods prohibited under Philippine laws and regulations.',
        ]),
      ]),
      _Section('1.2 Prohibited Buyer Activities', [
        _Bullets([
          'Use fake identities or fraudulent accounts.',
          'Submit fake orders or payment confirmations.',
          'Abuse vouchers, discounts, or promotional campaigns.',
          'Make false return, refund, or delivery claims.',
          'Harass, threaten, or abuse sellers, riders, couriers, or other users.',
          "Attempt to access another user's account.",
          'Use Cartzy to conduct illegal transactions.',
          'Manipulate ratings, reviews, or order records.',
        ]),
        _Para(
            'Cartzy may remove prohibited listings, cancel transactions, restrict account access, or report illegal activities to the proper authorities when necessary.'),
      ]),
    ],
  ),
];

// ──────────────────────────────────────────────────────────────
// SCREEN
// ──────────────────────────────────────────────────────────────

/// Terms of Service, Privacy Policy, Return & Refund, and Prohibited Items,
/// one tab each.
class TermsPoliciesScreen extends StatelessWidget {
  final int initialTab;

  const TermsPoliciesScreen({super.key, this.initialTab = 0});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: _policies.length,
      initialIndex: initialTab.clamp(0, _policies.length - 1),
      child: Scaffold(
        backgroundColor: CartzyColors.brand50Lavender,
        body: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 8, 16, 4),
                child: Row(
                  children: [
                    IconButton(
                      tooltip: 'Back',
                      onPressed: () => Navigator.of(context).pop(),
                      icon: const Icon(Icons.arrow_back_ios_new_rounded,
                          size: 18, color: _Palette.ink),
                    ),
                    const SizedBox(width: 2),
                    Text(
                      'Terms & Policies',
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: _Palette.ink,
                      ),
                    ),
                  ],
                ),
              ),
              TabBar(
                isScrollable: true,
                tabAlignment: TabAlignment.start,
                padding: const EdgeInsets.symmetric(horizontal: 12),
                labelPadding: const EdgeInsets.symmetric(horizontal: 4),
                dividerColor: Colors.transparent,
                indicatorSize: TabBarIndicatorSize.tab,
                splashBorderRadius: BorderRadius.circular(20),
                indicator: BoxDecoration(
                  gradient: _Palette.blend,
                  borderRadius: BorderRadius.circular(20),
                ),
                labelColor: Colors.white,
                unselectedLabelColor: _Palette.lavender,
                labelStyle: GoogleFonts.dmSans(
                    fontSize: 12.5, fontWeight: FontWeight.w700),
                unselectedLabelStyle: GoogleFonts.dmSans(
                    fontSize: 12.5, fontWeight: FontWeight.w600),
                tabs: [
                  for (final p in _policies)
                    Tab(
                      height: 36,
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        child: Text(p.tab),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 8),
              Expanded(
                child: TabBarView(
                  children: [for (final p in _policies) _PolicyView(p)],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PolicyView extends StatelessWidget {
  final _Policy policy;

  const _PolicyView(this.policy);

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
      children: [
        // Title card
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFFFFF7F2), Color(0xFFFDECEE)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: CartzyColors.rose200, width: 1.2),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      gradient: _Palette.blend,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(policy.icon, size: 20, color: Colors.white),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      policy.title,
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 19,
                        fontWeight: FontWeight.w700,
                        color: _Palette.ink,
                      ),
                    ),
                  ),
                ],
              ),
              if (policy.intro != null) ...[
                const SizedBox(height: 12),
                Text(
                  policy.intro!,
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    height: 1.55,
                    color: _Palette.body,
                  ),
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: 12),
        for (final section in policy.sections) _SectionCard(section),
      ],
    );
  }
}

class _SectionCard extends StatelessWidget {
  final _Section section;

  const _SectionCard(this.section);

  @override
  Widget build(BuildContext context) {
    final children = <Widget>[
      Text(
        section.title,
        style: GoogleFonts.dmSans(
          fontSize: 14.5,
          fontWeight: FontWeight.w800,
          color: _Palette.ink,
        ),
      ),
    ];
    for (final block in section.blocks) {
      children
        ..add(const SizedBox(height: 10))
        ..add(_buildBlock(block));
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _Palette.line),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: children,
      ),
    );
  }

  static TextStyle get _bodyStyle => GoogleFonts.dmSans(
        fontSize: 13,
        height: 1.55,
        color: _Palette.body,
      );

  Widget _buildBlock(_Block block) {
    if (block is _Para) {
      return Text(block.text, style: _bodyStyle);
    }
    if (block is _Bullets) {
      return Column(
        children: [
          for (final entry in block.items)
            if (entry is _Item || entry is String)
              Padding(
                padding: const EdgeInsets.only(bottom: 7),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 6,
                      height: 6,
                      margin: const EdgeInsets.only(top: 7, right: 10),
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: _Palette.blend,
                      ),
                    ),
                    Expanded(
                      child: Text.rich(
                        TextSpan(
                          style: _bodyStyle,
                          children: [
                            if (entry is _Item && entry.lead != null)
                              TextSpan(
                                text: '${entry.lead} ',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                  color: _Palette.ink,
                                ),
                              ),
                            TextSpan(
                                text: entry is _Item ? entry.text : '$entry'),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
        ],
      );
    }
    if (block is _Steps) {
      return Column(
        children: [
          for (var i = 0; i < block.steps.length; i++)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 22,
                    height: 22,
                    margin: const EdgeInsets.only(right: 10),
                    alignment: Alignment.center,
                    decoration: const BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: _Palette.blend,
                    ),
                    child: Text(
                      '${i + 1}',
                      style: GoogleFonts.dmSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.only(top: 1),
                      child: Text(block.steps[i], style: _bodyStyle),
                    ),
                  ),
                ],
              ),
            ),
        ],
      );
    }
    if (block is _Table) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: Table(
          border: const TableBorder.symmetric(
            inside: BorderSide(color: _Palette.line),
          ),
          columnWidths: const {
            0: FlexColumnWidth(1.4),
            1: FlexColumnWidth(1),
          },
          children: [
            TableRow(
              decoration: const BoxDecoration(gradient: _Palette.blend),
              children: [
                for (final h in block.header)
                  Padding(
                    padding: const EdgeInsets.all(10),
                    child: Text(
                      h,
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
              ],
            ),
            for (var r = 0; r < block.rows.length; r++)
              TableRow(
                decoration: BoxDecoration(
                  color: r.isEven ? CartzyColors.brand100 : Colors.white,
                ),
                children: [
                  for (final cell in block.rows[r])
                    Padding(
                      padding: const EdgeInsets.all(10),
                      child: Text(
                        cell,
                        style: GoogleFonts.dmSans(
                          fontSize: 12.5,
                          color: _Palette.ink,
                        ),
                      ),
                    ),
                ],
              ),
          ],
        ),
      );
    }
    return const SizedBox.shrink();
  }
}
