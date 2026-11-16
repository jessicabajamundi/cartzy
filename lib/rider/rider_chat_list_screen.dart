import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/api_service.dart';
import 'rider_common_app_bar.dart';

class ChatThread {
  final int userId;
  final String name;
  final String role;
  final String lastMessage;
  final String lastMessageAt;

  ChatThread({
    required this.userId,
    required this.name,
    required this.role,
    required this.lastMessage,
    required this.lastMessageAt,
  });

  factory ChatThread.fromJson(Map<String, dynamic> json) {
    return ChatThread(
      userId: int.tryParse(json['user_id']?.toString() ?? '') ?? 0,
      name: json['name']?.toString() ?? 'User',
      role: json['role']?.toString() ?? '',
      lastMessage: json['last_message']?.toString() ?? '',
      lastMessageAt: json['last_message_at']?.toString() ?? '',
    );
  }
}

class RiderChatListScreen extends StatefulWidget {
  final String token;
  final int myUserId;
  final VoidCallback onBack;
  final VoidCallback? onLogout;
  final Future<Map<String, dynamic>> Function()? loadMessages;
  final Future<void> Function(String)? sendMessage;

  const RiderChatListScreen(
      {super.key,
      required this.token,
      required this.myUserId,
      required this.onBack,
      this.onLogout,
      this.loadMessages,
      this.sendMessage});

  @override
  State<RiderChatListScreen> createState() => _RiderChatListScreenState();
}

class _RiderChatListScreenState extends State<RiderChatListScreen> {
  final _input = TextEditingController();
  List<Map<String, dynamic>> _messages = [];
  String? _hubName;
  String? _error;
  bool _loading = true;
  bool _sending = false;
  bool _fetching = false;
  Timer? _poll;

  @override
  void initState() {
    super.initState();
    _load();
    _poll =
        Timer.periodic(const Duration(seconds: 5), (_) => _load(silent: true));
  }

  @override
  void dispose() {
    _poll?.cancel();
    _input.dispose();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (_fetching) return;
    _fetching = true;
    if (!silent) {
      setState(() {
        _loading = true;
        _error = null;
      });
    }
    try {
      final data = await (widget.loadMessages?.call() ??
          ApiService.get('/rider/dispatch/messages', token: widget.token));
      if (!mounted) return;
      setState(() {
        _hubName = data['hub_name']?.toString();
        _messages = (data['messages'] as List? ?? [])
            .map((m) => Map<String, dynamic>.from(m as Map))
            .toList();
        _loading = false;
        _error = null;
      });
    } catch (e) {
      if (mounted) {
        setState(() {
          _loading = false;
          _error = e is ApiException
              ? e.message
              : 'Could not load your hub messages. Retry when connected.';
        });
      }
    } finally {
      _fetching = false;
    }
  }

  Future<void> _send() async {
    final text = _input.text.trim();
    if (text.isEmpty || _sending || _hubName == null || _error != null) return;
    setState(() => _sending = true);
    try {
      if (widget.sendMessage != null) {
        await widget.sendMessage!(text);
      } else {
        await ApiService.post('/rider/dispatch/messages', {'message': text},
            token: widget.token);
      }
      if (!mounted) return;
      _input.clear();
      await _load(silent: true);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(e is ApiException
                ? e.message
                : 'Message was not confirmed. Check your connection before retrying.')));
      }
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: CartzyColors.bgOf(context),
        body: SafeArea(
            child: Column(children: [
          RiderCommonAppBar(
              title: 'Hub messages',
              sector: _hubName ?? 'Your assigned hub',
              onBack: widget.onBack,
              onLogout: widget.onLogout),
          if (_error != null)
            MaterialBanner(content: Text(_error!), actions: [
              TextButton(onPressed: () => _load(), child: const Text('Retry'))
            ]),
          Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : _messages.isEmpty
                      ? const Center(
                          child: Text(
                              'No messages yet. Send an update to your logistics hub.'))
                      : ListView.builder(
                          reverse: true,
                          padding: const EdgeInsets.all(16),
                          itemCount: _messages.length,
                          itemBuilder: (context, index) {
                            final message =
                                _messages[_messages.length - 1 - index];
                            final mine = message['is_me'] == true;
                            final date = DateTime.tryParse(
                                    message['created_at']?.toString() ?? '')
                                ?.toLocal();
                            return Align(
                                alignment: mine
                                    ? Alignment.centerRight
                                    : Alignment.centerLeft,
                                child: Container(
                                  constraints: BoxConstraints(
                                      maxWidth:
                                          MediaQuery.sizeOf(context).width *
                                              .8),
                                  margin: const EdgeInsets.only(bottom: 10),
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                      color: CartzyColors.surfaceOf(context),
                                      borderRadius: BorderRadius.circular(14),
                                      border: Border.all(
                                          color:
                                              CartzyColors.borderOf(context))),
                                  child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                            mine
                                                ? 'You'
                                                : (_hubName ?? 'Logistics hub'),
                                            style: TextStyle(
                                                fontWeight: FontWeight.w700,
                                                color: CartzyColors.textOf(
                                                    context))),
                                        const SizedBox(height: 4),
                                        Text(message['body']?.toString() ?? '',
                                            style: TextStyle(
                                                color: CartzyColors.textOf(
                                                    context))),
                                        if (date != null)
                                          Text(
                                              MaterialLocalizations.of(context)
                                                  .formatTimeOfDay(
                                                      TimeOfDay.fromDateTime(
                                                          date)),
                                              style: TextStyle(
                                                  fontSize: 11,
                                                  color: CartzyColors.mutedOf(
                                                      context))),
                                      ]),
                                ));
                          })),
          Padding(
              padding: const EdgeInsets.all(12),
              child: Row(children: [
                Expanded(
                    child: TextField(
                        controller: _input,
                        enabled:
                            !_sending && _hubName != null && _error == null,
                        maxLength: 2000,
                        textInputAction: TextInputAction.send,
                        onSubmitted: (_) => _send(),
                        decoration: const InputDecoration(
                            hintText: 'Message your logistics hub',
                            counterText: ''))),
                IconButton(
                    tooltip: 'Send message',
                    onPressed: _sending || _hubName == null || _error != null
                        ? null
                        : _send,
                    icon: _sending
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2))
                        : const Icon(Icons.send_rounded)),
              ])),
        ])),
      );
}
