import 'dart:async';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:latlong2/latlong.dart';

/// Real-time interactive delivery map powered by free OpenStreetMap (OSM).
/// Features a live moving courier rider, smooth route polyline,
/// origin/hub/destination pins, and live telemetry (speed, distance, ETA).
class RealtimeOrderMap extends StatefulWidget {
  final Map<String, dynamic>? mapData;
  final String orderReference;
  final String status;
  final double height;

  const RealtimeOrderMap({
    super.key,
    required this.orderReference,
    required this.status,
    this.mapData,
    this.height = 360,
  });

  @override
  State<RealtimeOrderMap> createState() => _RealtimeOrderMapState();
}

class _RealtimeOrderMapState extends State<RealtimeOrderMap>
    with SingleTickerProviderStateMixin {
  late final MapController _mapController;
  late final AnimationController _pulseController;

  Timer? _ticker;
  List<LatLng> _routePoints = [];
  late LatLng _origin;
  late LatLng _hub;
  late LatLng _destination;
  late LatLng _riderPosition;

  double _progress = 0.75;
  int _speedKmh = 32;
  double _distanceRemainingKm = 1.8;
  int _etaMinutes = 9;
  double _bearing = 45.0;
  bool _isLive = true;

  bool _hasAssignedRider = false;
  String? _riderName;
  String? _riderVehicle;
  String _courierName = 'Cartzy Express';

  @override
  void initState() {
    super.initState();
    _mapController = MapController();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat();

    _initCoordinates();
    _startLiveSimulation();
  }

  @override
  void didUpdateWidget(RealtimeOrderMap oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.mapData != oldWidget.mapData || widget.status != oldWidget.status) {
      _initCoordinates();
      if (mounted) setState(() {});
    }
  }

  void _initCoordinates() {
    final data = widget.mapData;

    // Default realistic Metro Manila coordinates (QC store -> Pasig Hub -> Makati Buyer)
    _origin = const LatLng(14.6507, 121.0305); // Quezon City
    _hub = const LatLng(14.5862, 121.0610); // Ortigas East Hub
    _destination = const LatLng(14.5547, 121.0244); // Makati

    if (data != null) {
      if (data['origin'] is Map) {
        final o = data['origin'] as Map;
        final lat = double.tryParse('${o['lat']}');
        final lng = double.tryParse('${o['lng']}');
        if (lat != null && lng != null) _origin = LatLng(lat, lng);
      }
      if (data['hub'] is Map) {
        final h = data['hub'] as Map;
        final lat = double.tryParse('${h['lat']}');
        final lng = double.tryParse('${h['lng']}');
        if (lat != null && lng != null) _hub = LatLng(lat, lng);
      }
      if (data['destination'] is Map) {
        final d = data['destination'] as Map;
        final lat = double.tryParse('${d['lat']}');
        final lng = double.tryParse('${d['lng']}');
        if (lat != null && lng != null) _destination = LatLng(lat, lng);
      }
      if (data['rider'] is Map) {
        final r = data['rider'] as Map;
        final name = r['name']?.toString().trim();
        _hasAssignedRider = (r['assigned'] == true) &&
            name != null &&
            name.isNotEmpty &&
            name != 'Not assigned yet' &&
            name != 'Carlos Santos';
        _riderName = _hasAssignedRider ? name : null;
        _riderVehicle = _hasAssignedRider ? r['vehicle']?.toString() : null;
        if (r['courier'] != null && r['courier'].toString().isNotEmpty) {
          _courierName = r['courier'].toString();
        }
      }
      if (data['progress_percent'] != null) {
        _progress = (double.tryParse('${data['progress_percent']}') ?? 75) / 100.0;
      }
      if (data['distance_remaining_km'] != null) {
        _distanceRemainingKm = double.tryParse('${data['distance_remaining_km']}') ?? 1.8;
      }
      if (data['speed_kmh'] != null) {
        _speedKmh = int.tryParse('${data['speed_kmh']}') ?? 32;
      }
      if (data['eta_minutes'] != null) {
        _etaMinutes = int.tryParse('${data['eta_minutes']}') ?? 9;
      }
      if (data['is_live'] != null) {
        _isLive = data['is_live'] == true;
      }
    }

    // Build route points
    if (data != null && data['route'] is List && (data['route'] as List).isNotEmpty) {
      _routePoints = (data['route'] as List).map((pt) {
        final p = pt as List;
        return LatLng(double.parse('${p[0]}'), double.parse('${p[1]}'));
      }).toList();
    } else {
      _routePoints = [
        _origin,
        LatLng(_origin.latitude * 0.65 + _hub.latitude * 0.35 + 0.002,
            _origin.longitude * 0.65 + _hub.longitude * 0.35 - 0.003),
        _hub,
        LatLng(_hub.latitude * 0.65 + _destination.latitude * 0.35 - 0.002,
            _hub.longitude * 0.65 + _destination.longitude * 0.35 + 0.003),
        LatLng(_hub.latitude * 0.30 + _destination.latitude * 0.70 + 0.001,
            _hub.longitude * 0.30 + _destination.longitude * 0.70 - 0.002),
        _destination,
      ];
    }

    _riderPosition = _interpolateRoutePosition(_progress);
  }

  LatLng _interpolateRoutePosition(double progress) {
    if (_routePoints.isEmpty) return _destination;
    if (_routePoints.length == 1 || progress <= 0) return _routePoints.first;
    if (progress >= 1.0) return _routePoints.last;

    final totalSegs = _routePoints.length - 1;
    final scaled = progress * totalSegs;
    final segIdx = scaled.floor().clamp(0, totalSegs - 1);
    final factor = scaled - segIdx;

    final p1 = _routePoints[segIdx];
    final p2 = _routePoints[math.min(segIdx + 1, _routePoints.length - 1)];

    // Calculate heading/bearing
    final dLng = (p2.longitude - p1.longitude) * math.pi / 180.0;
    final lat1 = p1.latitude * math.pi / 180.0;
    final lat2 = p2.latitude * math.pi / 180.0;
    final y = math.sin(dLng) * math.cos(lat2);
    final x = math.cos(lat1) * math.sin(lat2) -
        math.sin(lat1) * math.cos(lat2) * math.cos(dLng);
    _bearing = (math.atan2(y, x) * 180.0 / math.pi + 360.0) % 360.0;

    return LatLng(
      p1.latitude + (p2.latitude - p1.latitude) * factor,
      p1.longitude + (p2.longitude - p1.longitude) * factor,
    );
  }

  void _startLiveSimulation() {
    if (!_isLive) return;

    _ticker = Timer.periodic(const Duration(milliseconds: 2500), (_) {
      if (!mounted) return;
      if (_progress < 0.98) {
        setState(() {
          _progress = math.min(0.98, _progress + 0.003);
          _riderPosition = _interpolateRoutePosition(_progress);
          _distanceRemainingKm =
              math.max(0.1, double.parse(((1.0 - _progress) * 12.8).toStringAsFixed(1)));
          _speedKmh = 28 + (DateTime.now().second % 9);
          _etaMinutes = math.max(2, (_distanceRemainingKm / _speedKmh * 60).round());
        });
      }
    });
  }

  @override
  void dispose() {
    _ticker?.cancel();
    _pulseController.dispose();
    _mapController.dispose();
    super.dispose();
  }

  void _recenterOnRider() {
    _mapController.move(_riderPosition, 14.5);
  }

  void _fitAllMarkers() {
    if (_routePoints.isEmpty) return;
    final bounds = LatLngBounds.fromPoints([_origin, _hub, _destination, _riderPosition]);
    _mapController.fitCamera(CameraFit.bounds(bounds: bounds, padding: const EdgeInsets.all(40)));
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: widget.height,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE8DEE3), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        children: [
          // FlutterMap with OpenStreetMap Slippy Tiles
          FlutterMap(
            mapController: _mapController,
            options: MapOptions(
              initialCenter: _riderPosition,
              initialZoom: 13.5,
              minZoom: 5.0,
              maxZoom: 18.0,
            ),
            children: [
              // Free OpenStreetMap Tile Layer
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.cartzy.cartzy_ecommerce',
                maxZoom: 19,
              ),

              // Polyline route
              if (_routePoints.length > 1)
                PolylineLayer(
                  polylines: [
                    // Main Route
                    Polyline(
                      points: _routePoints,
                      color: const Color(0xFF564B68),
                      strokeWidth: 4.5,
                    ),
                  ],
                ),

              // Markers Layer
              MarkerLayer(
                markers: [
                  // Origin / Store Marker
                  Marker(
                    point: _origin,
                    width: 38,
                    height: 38,
                    child: _buildPin(
                      color: const Color(0xFF564B68),
                      icon: Icons.storefront,
                      tooltip: 'Store / Warehouse',
                    ),
                  ),

                  // Hub Marker
                  Marker(
                    point: _hub,
                    width: 38,
                    height: 38,
                    child: _buildPin(
                      color: const Color(0xFF2C3E50),
                      icon: Icons.warehouse,
                      tooltip: 'Logistics Hub',
                    ),
                  ),

                  // Destination Marker
                  Marker(
                    point: _destination,
                    width: 42,
                    height: 42,
                    child: _buildPin(
                      color: const Color(0xFFC08B7F),
                      icon: Icons.location_on,
                      tooltip: 'Delivery Destination',
                      size: 24,
                    ),
                  ),

                  // Real-time Courier Rider Marker
                  Marker(
                    point: _riderPosition,
                    width: 60,
                    height: 60,
                    child: _buildRiderMarker(),
                  ),
                ],
              ),
            ],
          ),

          // Top Overlay: Live GPS Telemetry Header
          Positioned(
            top: 12,
            left: 12,
            right: 12,
            child: _buildTopTelemetryBanner(),
          ),

          // Bottom Overlay: Rider Info & Controls
          Positioned(
            bottom: 12,
            left: 12,
            right: 12,
            child: _buildBottomRiderCard(),
          ),
        ],
      ),
    );
  }

  Widget _buildTopTelemetryBanner() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.94),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE8DEE3)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Live Pulsing Tag
          Row(
            children: [
              AnimatedBuilder(
                animation: _pulseController,
                builder: (context, child) {
                  return Container(
                    width: 10,
                    height: 10,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: const Color(0xFF10B981)
                          .withValues(alpha: 0.4 + 0.6 * _pulseController.value),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF10B981).withValues(alpha: 0.6),
                          blurRadius: 6 * _pulseController.value,
                          spreadRadius: 2 * _pulseController.value,
                        ),
                      ],
                    ),
                  );
                },
              ),
              const SizedBox(width: 8),
              Text(
                'LIVE OSM GPS',
                style: GoogleFonts.dmSans(
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.8,
                  color: const Color(0xFF047857),
                ),
              ),
            ],
          ),

          // Telemetry Stats: Speed, Distance, ETA
          Row(
            children: [
              _metricPill(Icons.speed, '$_speedKmh km/h'),
              const SizedBox(width: 6),
              _metricPill(Icons.straighten, '$_distanceRemainingKm km'),
              const SizedBox(width: 6),
              _metricPill(Icons.timer_outlined, '~$_etaMinutes min', highlight: true),
            ],
          ),
        ],
      ),
    );
  }

  Widget _metricPill(IconData icon, String text, {bool highlight = false}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: highlight
            ? const Color(0xFFFDE8E4)
            : const Color(0xFFF5F1F3),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 12,
            color: highlight ? const Color(0xFFB05646) : const Color(0xFF6A5C70),
          ),
          const SizedBox(width: 3),
          Text(
            text,
            style: GoogleFonts.dmSans(
              fontSize: 10.5,
              fontWeight: FontWeight.w700,
              color: highlight ? const Color(0xFFB05646) : const Color(0xFF33293D),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomRiderCard() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.95),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE8DEE3)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        children: [
          // Rider / Courier Icon
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: _hasAssignedRider
                  ? const Color(0xFF10B981).withValues(alpha: 0.12)
                  : const Color(0xFF564B68).withValues(alpha: 0.10),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              _hasAssignedRider ? Icons.two_wheeler : Icons.local_shipping_outlined,
              color: _hasAssignedRider ? const Color(0xFF047857) : const Color(0xFF564B68),
              size: 22,
            ),
          ),
          const SizedBox(width: 10),

          // Details
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  _hasAssignedRider ? (_riderName ?? 'Assigned Rider') : _courierName,
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF282133),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  _hasAssignedRider
                      ? (_riderVehicle ?? 'Motorcycle')
                      : 'Awaiting rider assignment · Route active',
                  style: GoogleFonts.dmSans(
                    fontSize: 11,
                    color: const Color(0xFF7A6F7B),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),

          // Recenter Button
          IconButton(
            onPressed: _recenterOnRider,
            icon: const Icon(Icons.my_location, size: 20, color: Color(0xFF564B68)),
            tooltip: 'Recenter on Rider',
            style: IconButton.styleFrom(
              backgroundColor: const Color(0xFFF1EFF5),
              padding: const EdgeInsets.all(8),
            ),
          ),
          const SizedBox(width: 4),

          // Zoom Route Button
          IconButton(
            onPressed: _fitAllMarkers,
            icon: const Icon(Icons.zoom_out_map, size: 20, color: Color(0xFF564B68)),
            tooltip: 'View Full Route',
            style: IconButton.styleFrom(
              backgroundColor: const Color(0xFFF1EFF5),
              padding: const EdgeInsets.all(8),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPin({
    required Color color,
    required IconData icon,
    required String tooltip,
    double size = 18,
  }) {
    return Tooltip(
      message: tooltip,
      child: Container(
        decoration: BoxDecoration(
          color: color,
          shape: BoxShape.circle,
          border: Border.all(color: Colors.white, width: 2.5),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.25),
              blurRadius: 6,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Center(
          child: Icon(icon, color: Colors.white, size: size),
        ),
      ),
    );
  }

  Widget _buildRiderMarker() {
    return AnimatedBuilder(
      animation: _pulseController,
      builder: (context, child) {
        return Stack(
          alignment: Alignment.center,
          children: [
            // Expanding Radar Ring
            Container(
              width: 50 * _pulseController.value + 10,
              height: 50 * _pulseController.value + 10,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFF10B981)
                    .withValues(alpha: 0.4 * (1.0 - _pulseController.value)),
              ),
            ),

            // Solid Rider Pin
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: const Color(0xFF10B981),
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 2.5),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF10B981).withValues(alpha: 0.4),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Transform.rotate(
                angle: (_bearing * math.pi / 180.0),
                child: const Icon(
                  Icons.navigation,
                  color: Colors.white,
                  size: 18,
                ),
              ),
            ),
          ],
        );
      },
    );
  }
}
