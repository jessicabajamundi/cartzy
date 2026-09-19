import 'package:flutter/material.dart';
import 'cartzy_colors.dart';

class CheckoutScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onOrderPlaced;

  const CheckoutScreen({
    super.key,
    required this.onBack,
    required this.onOrderPlaced,
  });

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  // --------------------------------------------------------
  // CONTROLLERS
  // --------------------------------------------------------

  final TextEditingController houseNumberController =
      TextEditingController(text: '123');

  final TextEditingController streetController =
      TextEditingController(text: 'Main Street');

  final TextEditingController contactController =
      TextEditingController(text: '09123456789');

  final TextEditingController gcashController =
      TextEditingController();

  // --------------------------------------------------------
  // PAYMENT METHOD
  // --------------------------------------------------------

  String selectedPayment = 'COD';

  // --------------------------------------------------------
  // SAMPLE ORDER DATA
  // --------------------------------------------------------

  final List<Map<String, dynamic>> orderItems = [
    {
      'name': 'Wireless Earbuds',
      'price': 29.99,
      'quantity': 1,
    },
    {
      'name': 'Running Shoes',
      'price': 49.99,
      'quantity': 1,
    },
    {
      'name': 'Smart Watch',
      'price': 59.99,
      'quantity': 1,
    },
  ];

  double get subtotal {
    double total = 0;

    for (final item in orderItems) {
      total +=
          (item['price'] as double) *
          (item['quantity'] as int);
    }

    return total;
  }

  final double deliveryFee = 5.00;

  double get total {
    return subtotal + deliveryFee;
  }

  // --------------------------------------------------------
  // DISPOSE
  // --------------------------------------------------------

  @override
  void dispose() {
    houseNumberController.dispose();
    streetController.dispose();
    contactController.dispose();
    gcashController.dispose();

    super.dispose();
  }

  // --------------------------------------------------------
  // PLACE ORDER
  // --------------------------------------------------------

  void placeOrder() {
    if (selectedPayment == 'GCash' &&
        gcashController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Please enter your GCash number.',
          ),
        ),
      );

      return;
    }

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
          title: const Row(
            children: [
              Icon(
                Icons.check_circle,
                color: Colors.green,
                size: 30,
              ),
              SizedBox(width: 10),
              Text('Order Placed'),
            ],
          ),
          content: Text(
            selectedPayment == 'GCash'
                ? 'Your order has been placed using GCash.'
                : 'Your order has been placed using Cash on Delivery.',
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(context);
                widget.onOrderPlaced();
              },
              child: const Text('OK'),
            ),
          ],
        );
      },
    );
  }

  // --------------------------------------------------------
  // NEUMORPHIC BUTTON
  // --------------------------------------------------------

  Widget _buildNeumorphicButton({
    required String text,
    required VoidCallback onPressed,
    IconData? icon,
  }) {
    return Container(
      width: double.infinity,
      height: 52,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        boxShadow: const [
          BoxShadow(
            color: Colors.black26,
            offset: Offset(4, 5),
            blurRadius: 6,
          ),
        ],
      ),
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: CartzyColors.coral,
          foregroundColor: Colors.white,
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (icon != null) ...[
              Icon(icon),
              const SizedBox(width: 8),
            ],
            Text(
              text,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // --------------------------------------------------------
  // SECTION TITLE
  // --------------------------------------------------------

  Widget _sectionTitle(
    String title,
    IconData icon,
  ) {
    return Row(
      children: [
        Icon(
          icon,
          color: CartzyColors.navy,
          size: 22,
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: CartzyColors.navy,
          ),
        ),
      ],
    );
  }

  // --------------------------------------------------------
  // TEXT FIELD
  // --------------------------------------------------------

  Widget _textField({
    required String label,
    required TextEditingController controller,
    TextInputType keyboardType =
        TextInputType.text,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: TextField(
        controller: controller,
        keyboardType: keyboardType,
        decoration: InputDecoration(
          labelText: label,
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(
              color: CartzyColors.border,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(
              color: CartzyColors.border,
            ),
          ),
        ),
      ),
    );
  }

  // --------------------------------------------------------
  // PAYMENT OPTION
  // --------------------------------------------------------

  Widget _paymentOption({
    required String title,
    required String subtitle,
    required String value,
    required IconData icon,
  }) {
    final bool selected =
        selectedPayment == value;

    return GestureDetector(
      onTap: () {
        setState(() {
          selectedPayment = value;
        });
      },
      child: AnimatedContainer(
        duration:
            const Duration(milliseconds: 180),
        margin:
            const EdgeInsets.only(bottom: 12),
        padding:
            const EdgeInsets.all(15),
        decoration: BoxDecoration(
          color: selected
              ? CartzyColors.coral.withValues(alpha: 0.08)
              : Colors.white,
          borderRadius:
              BorderRadius.circular(14),
          border: Border.all(
            color: selected
                ? CartzyColors.coral
                : CartzyColors.border,
            width: selected ? 2 : 1,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 45,
              height: 45,
              decoration: BoxDecoration(
                color: selected
                    ? CartzyColors.coral
                    : CartzyColors.background,
                borderRadius:
                    BorderRadius.circular(12),
              ),
              child: Icon(
                icon,
                color: selected
                    ? Colors.white
                    : CartzyColors.navy,
              ),
            ),

            const SizedBox(width: 12),

            Expanded(
              child: Column(
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color:
                          CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 12,
                      color:
                          CartzyColors.gray,
                    ),
                  ),
                ],
              ),
            ),

            Radio<String>(
              value: value,
              groupValue: selectedPayment,
              activeColor:
                  CartzyColors.coral,
              onChanged: (value) {
                if (value == null) return;

                setState(() {
                  selectedPayment = value;
                });
              },
            ),
          ],
        ),
      ),
    );
  }

  // --------------------------------------------------------
  // ORDER ITEM
  // --------------------------------------------------------

  Widget _orderItem(
    Map<String, dynamic> item,
  ) {
    final double price =
        item['price'] as double;

    final int quantity =
        item['quantity'] as int;

    return Padding(
      padding:
          const EdgeInsets.symmetric(
        vertical: 8,
      ),
      child: Row(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: CartzyColors.background,
              borderRadius:
                  BorderRadius.circular(10),
            ),
            child: const Icon(
              Icons.shopping_bag_outlined,
              color: CartzyColors.navy,
            ),
          ),

          const SizedBox(width: 12),

          Expanded(
            child: Column(
              crossAxisAlignment:
                  CrossAxisAlignment.start,
              children: [
                Text(
                  item['name'],
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  'Qty: $quantity',
                  style: const TextStyle(
                    fontSize: 12,
                    color: CartzyColors.gray,
                  ),
                ),
              ],
            ),
          ),

          Text(
            '\$${(price * quantity).toStringAsFixed(2)}',
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),
        ],
      ),
    );
  }

  // --------------------------------------------------------
  // BUILD
  // --------------------------------------------------------

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor:
          CartzyColors.background,

      appBar: AppBar(
        backgroundColor:
            CartzyColors.background,
        elevation: 0,
        centerTitle: true,

        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back,
            color: CartzyColors.navy,
          ),
          onPressed: widget.onBack,
        ),

        title: const Text(
          'Checkout',
          style: TextStyle(
            color: CartzyColors.navy,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),

      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(
            20,
            10,
            20,
            30,
          ),
          child: Column(
            crossAxisAlignment:
                CrossAxisAlignment.start,
            children: [

              // =================================================
              // DELIVERY ADDRESS
              // =================================================

              _sectionTitle(
                'Delivery Address',
                Icons.location_on_outlined,
              ),

              const SizedBox(height: 15),

              Container(
                padding:
                    const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius:
                      BorderRadius.circular(16),
                  border: Border.all(
                    color:
                        CartzyColors.border,
                  ),
                ),
                child: Column(
                  children: [

                    _textField(
                      label:
                          'House Number',
                      controller:
                          houseNumberController,
                    ),

                    _textField(
                      label:
                          'Street',
                      controller:
                          streetController,
                    ),

                    _textField(
                      label:
                          'Contact Number',
                      controller:
                          contactController,
                      keyboardType:
                          TextInputType.phone,
                    ),

                    const Row(
                      children: [
                        Icon(
                          Icons.location_city,
                          size: 18,
                          color:
                              CartzyColors.gray,
                        ),
                        SizedBox(width: 6),
                        Expanded(
                          child: Text(
                            'Rochdale, Greater Manchester',
                            style: TextStyle(
                              color:
                                  CartzyColors.gray,
                              fontSize: 13,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 25),

              // =================================================
              // PAYMENT METHOD
              // =================================================

              _sectionTitle(
                'Payment Method',
                Icons.payment_outlined,
              ),

              const SizedBox(height: 15),

              _paymentOption(
                title: 'Cash on Delivery',
                subtitle:
                    'Pay when your order arrives',
                value: 'COD',
                icon:
                    Icons.local_atm_outlined,
              ),

              _paymentOption(
                title: 'GCash',
                subtitle:
                    'Pay using your GCash account',
                value: 'GCash',
                icon:
                    Icons.account_balance_wallet_outlined,
              ),

              // =================================================
              // GCASH NUMBER
              // =================================================

              if (selectedPayment == 'GCash') ...[
                const SizedBox(height: 5),

                Container(
                  padding:
                      const EdgeInsets.all(15),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius:
                        BorderRadius.circular(14),
                    border: Border.all(
                      color:
                          CartzyColors.border,
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment:
                        CrossAxisAlignment.start,
                    children: [

                      const Text(
                        'GCash Details',
                        style: TextStyle(
                          fontWeight:
                              FontWeight.bold,
                          color:
                              CartzyColors.navy,
                        ),
                      ),

                      const SizedBox(height: 10),

                      TextField(
                        controller:
                            gcashController,
                        keyboardType:
                            TextInputType.phone,
                        decoration:
                            InputDecoration(
                          labelText:
                              'GCash Number',
                          hintText:
                              '09XXXXXXXXX',
                          prefixIcon:
                              const Icon(
                            Icons.phone,
                          ),
                          border:
                              OutlineInputBorder(
                            borderRadius:
                                BorderRadius
                                    .circular(
                              12,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 25),

              // =================================================
              // ORDER SUMMARY
              // =================================================

              _sectionTitle(
                'Order Summary',
                Icons.receipt_long_outlined,
              ),

              const SizedBox(height: 15),

              Container(
                padding:
                    const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius:
                      BorderRadius.circular(16),
                  border: Border.all(
                    color:
                        CartzyColors.border,
                  ),
                ),
                child: Column(
                  children: [

                    ...orderItems.map(
                      (item) =>
                          _orderItem(item),
                    ),

                    const Divider(
                      height: 25,
                    ),

                    Row(
                      mainAxisAlignment:
                          MainAxisAlignment
                              .spaceBetween,
                      children: [
                        const Text(
                          'Subtotal',
                          style: TextStyle(
                            color:
                                CartzyColors.gray,
                          ),
                        ),
                        Text(
                          '\$${subtotal.toStringAsFixed(2)}',
                          style:
                              const TextStyle(
                            fontWeight:
                                FontWeight.w600,
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 10),

                    Row(
                      mainAxisAlignment:
                          MainAxisAlignment
                              .spaceBetween,
                      children: [
                        const Text(
                          'Delivery Fee',
                          style: TextStyle(
                            color:
                                CartzyColors.gray,
                          ),
                        ),
                        Text(
                          '\$${deliveryFee.toStringAsFixed(2)}',
                          style:
                              const TextStyle(
                            fontWeight:
                                FontWeight.w600,
                          ),
                        ),
                      ],
                    ),

                    const Divider(
                      height: 25,
                    ),

                    Row(
                      mainAxisAlignment:
                          MainAxisAlignment
                              .spaceBetween,
                      children: [
                        const Text(
                          'Total',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight:
                                FontWeight.bold,
                            color:
                                CartzyColors.navy,
                          ),
                        ),
                        Text(
                          '\$${total.toStringAsFixed(2)}',
                          style:
                              const TextStyle(
                            fontSize: 20,
                            fontWeight:
                                FontWeight.bold,
                            color:
                                CartzyColors.coral,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 30),

              // =================================================
              // PLACE ORDER
              // =================================================

              _buildNeumorphicButton(
                text: 'Place Order',
                icon:
                    Icons.check_circle_outline,
                onPressed: placeOrder,
              ),

              const SizedBox(height: 12),

              // =================================================
              // BACK TO CART
              // =================================================

              SizedBox(
                width: double.infinity,
                height: 50,
                child: OutlinedButton(
                  onPressed: widget.onBack,
                  style:
                      OutlinedButton.styleFrom(
                    foregroundColor:
                        CartzyColors.navy,
                    side: const BorderSide(
                      color:
                          CartzyColors.navy,
                    ),
                    shape:
                        RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(
                        14,
                      ),
                    ),
                  ),
                  child: const Text(
                    'Back to Cart',
                    style: TextStyle(
                      fontWeight:
                          FontWeight.w600,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}