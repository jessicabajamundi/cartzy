import 'package:flutter/material.dart';
import 'cartzy_colors.dart';
import 'services/api_service.dart';

// ================================================================
// MODEL
// ================================================================

class PendingRider {
  final int id;
  final String name;
  final String email;
  final String phone;
  final String vehicleType;
  final String plateNumber;
  final String licenseNumber;
  final String? licensePhoto;
  final String? orCrPhoto;
  final String createdAt;

  PendingRider({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.vehicleType,
    required this.plateNumber,
    required this.licenseNumber,
    required this.licensePhoto,
    required this.orCrPhoto,
    required this.createdAt,
  });

  factory PendingRider.fromJson(Map<String, dynamic> json) {
    return PendingRider(
      id: int.tryParse(json['id']?.toString() ?? '') ?? 0,
      name: json['name']?.toString() ?? 'Unnamed',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      vehicleType: json['vehicle_type']?.toString() ?? '-',
      plateNumber: json['plate_number']?.toString() ?? '-',
      licenseNumber: json['license_number']?.toString() ?? '-',
      licensePhoto: json['license_photo']?.toString(),
      orCrPhoto: json['or_cr_photo']?.toString(),
      createdAt: json['created_at']?.toString() ?? '',
    );
  }
}

// ================================================================
// SCREEN
// ================================================================

class LogisticsDashboardScreen extends StatefulWidget {
  final String token;
  final String adminName;
  final VoidCallback onLogout;

  const LogisticsDashboardScreen({
    super.key,
    required this.token,
    required this.adminName,
    required this.onLogout,
  });

  @override
  State<LogisticsDashboardScreen> createState() => _LogisticsDashboardScreenState();
}

class _LogisticsDashboardScreenState extends State<LogisticsDashboardScreen> {
  List<PendingRider> _riders = [];
  bool _isLoading = true;
  String? _error;
  final Set<int> _processingIds = {};

  String get _uploadsOrigin =>
      ApiService.baseUrl.replaceFirst('/api', '');

  @override
  void initState() {
    super.initState();
    _loadPending();
  }

  // ============================================================
  // LOAD PENDING RIDERS
  // ============================================================

  Future<void> _loadPending() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await ApiService.getList(
        '/riders/pending',
        token: widget.token,
      );

      final riders = data
          .map((r) => PendingRider.fromJson(Map<String, dynamic>.from(r)))
          .toList();

      if (!mounted) return;
      setState(() {
        _riders = riders;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      final message = e.toString();
      if (message.contains('401') || message.contains('403')) {
        widget.onLogout();
        return;
      }

      setState(() {
        _isLoading = false;
        _error = message.replaceFirst('Exception: ', '');
      });
    }
  }

  // ============================================================
  // APPROVE / REJECT
  // ============================================================

  Future<void> _decide(PendingRider rider, bool approve) async {
    setState(() => _processingIds.add(rider.id));

    try {
      await ApiService.patch(
        '/riders/${rider.id}/approve',
        {'approve': approve},
        token: widget.token,
      );

      if (!mounted) return;

      setState(() {
        _riders.removeWhere((r) => r.id == rider.id);
        _processingIds.remove(rider.id);
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          behavior: SnackBarBehavior.floating,
          backgroundColor: approve ? Colors.green.shade700 : CartzyColors.navy,
          content: Text(
            approve
                ? '${rider.name} approved.'
                : '${rider.name} rejected.',
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;

      setState(() => _processingIds.remove(rider.id));

      final message = e.toString();
      if (message.contains('401') || message.contains('403')) {
        widget.onLogout();
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Failed to update rider: ${message.replaceFirst('Exception: ', '')}',
          ),
        ),
      );
    }
  }

  // ============================================================
  // PHOTO PREVIEW
  // ============================================================

  void _showPhoto(String title, String? filename) {
    if (filename == null || filename.isEmpty) return;

    final url = '$_uploadsOrigin/uploads/$filename';

    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(title),
        content: SizedBox(
          width: 320,
          child: Image.network(
            url,
            fit: BoxFit.contain,
            loadingBuilder: (context, child, progress) {
              if (progress == null) return child;
              return const Padding(
                padding: EdgeInsets.all(40),
                child: Center(child: CircularProgressIndicator()),
              );
            },
            errorBuilder: (context, error, stackTrace) => const Padding(
              padding: EdgeInsets.all(24),
              child: Text('Could not load image.'),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.background,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Text(
          'Cartzy Logistics',
          style: TextStyle(
            color: CartzyColors.navy,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: CartzyColors.navy),
            onPressed: _isLoading ? null : _loadPending,
            tooltip: 'Refresh',
          ),
          IconButton(
            icon: const Icon(Icons.logout, color: CartzyColors.navy),
            onPressed: widget.onLogout,
            tooltip: 'Log out',
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 4),
              child: Row(
                children: [
                  const Icon(Icons.admin_panel_settings,
                      size: 18, color: CartzyColors.gray),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Logged in as ${widget.adminName}',
                      style: const TextStyle(
                        color: CartzyColors.gray,
                        fontSize: 13,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(child: _buildBody()),
          ],
        ),
      ),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, color: Colors.redAccent, size: 40),
              const SizedBox(height: 12),
              Text(
                'Failed to load: $_error',
                textAlign: TextAlign.center,
                style: const TextStyle(color: CartzyColors.gray),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loadPending,
                child: const Text('Try Again'),
              ),
            ],
          ),
        ),
      );
    }

    if (_riders.isEmpty) {
      return RefreshIndicator(
        onRefresh: _loadPending,
        child: ListView(
          padding: const EdgeInsets.only(top: 100),
          children: const [
            Center(
              child: Column(
                children: [
                  Icon(Icons.check_circle_outline,
                      size: 44, color: CartzyColors.gray),
                  SizedBox(height: 12),
                  Text(
                    'No pending riders right now.',
                    style: TextStyle(color: CartzyColors.gray),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadPending,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
        itemCount: _riders.length,
        itemBuilder: (context, index) {
          final rider = _riders[index];
          final isProcessing = _processingIds.contains(rider.id);

          return Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: CartzyColors.surface,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: CartzyColors.border),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  rider.name,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${rider.email} · ${rider.phone}',
                  style: const TextStyle(fontSize: 12, color: CartzyColors.gray),
                ),
                const SizedBox(height: 10),
                _infoRow('Vehicle', rider.vehicleType),
                _infoRow('Plate Number', rider.plateNumber),
                _infoRow('License #', rider.licenseNumber),
                _infoLinkRow(
                  'License Photo',
                  rider.licensePhoto,
                  () => _showPhoto('License Photo', rider.licensePhoto),
                ),
                _infoLinkRow(
                  'OR/CR Photo',
                  rider.orCrPhoto,
                  () => _showPhoto('OR/CR Photo', rider.orCrPhoto),
                ),
                if (rider.createdAt.isNotEmpty)
                  _infoRow('Applied', rider.createdAt),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        onPressed: isProcessing ? null : () => _decide(rider, true),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green.shade700,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                        child: isProcessing
                            ? const SizedBox(
                                height: 16,
                                width: 16,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Text('Approve'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: isProcessing ? null : () => _decide(rider, false),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red.shade600,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                        child: const Text('Reject'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: RichText(
        text: TextSpan(
          style: const TextStyle(fontSize: 13, color: CartzyColors.navy),
          children: [
            TextSpan(
              text: '$label  ',
              style: const TextStyle(
                color: CartzyColors.gray,
                fontWeight: FontWeight.w600,
              ),
            ),
            TextSpan(text: value),
          ],
        ),
      ),
    );
  }

  Widget _infoLinkRow(String label, String? filename, VoidCallback onTap) {
    final hasFile = filename != null && filename.isNotEmpty;

    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        children: [
          SizedBox(
            width: 110,
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 13,
                color: CartzyColors.gray,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          GestureDetector(
            onTap: hasFile ? onTap : null,
            child: Text(
              hasFile ? 'View' : '-',
              style: TextStyle(
                fontSize: 13,
                color: hasFile ? CartzyColors.coral : CartzyColors.gray,
                fontWeight: hasFile ? FontWeight.w600 : FontWeight.normal,
                decoration: hasFile ? TextDecoration.underline : null,
              ),
            ),
          ),
        ],
      ),
    );
  }
}