@extends('layouts.admin')
@section('page_title', 'Messages')
@section('page_description', 'Send administrative messages to the notification inbox of registered users.')
@section('content')
<div class="message-grid"><section class="panel"><form class="filters" method="GET"><label class="search-field">Find a recipient<input name="search" value="{{ request('search') }}" placeholder="Search by name"></label><button class="button subtle">Search</button></form>
<div class="contact-list">@forelse($contacts as $contact)<a class="contact {{ $currentContact?->id === $contact->id ? 'selected' : '' }}" href="{{ route('admin.chat', array_merge(request()->only('search', 'page'), ['contact' => $contact->id])) }}"><span class="avatar">{{ mb_strtoupper(mb_substr($contact->name, 0, 1)) }}</span><div><strong>{{ $contact->name }}</strong><small>{{ ucfirst($contact->role) }} · {{ $contact->email }}</small></div></a>@empty<div class="empty"><strong>No matching recipients</strong><p>Only registered accounts appear here.</p></div>@endforelse</div>
@include('admin.partials.pagination', ['paginator' => $contacts])</section>
<section class="panel">
@if($currentContact)<header class="panel-header"><div><h2>{{ $currentContact->name }}</h2><p>{{ $currentContact->email }}</p></div><span class="badge">Notifications</span></header>
<form class="form-stack padded" action="{{ route('admin.chat.send', $currentContact->id) }}" method="POST">@csrf<label>New message<textarea name="message" required maxlength="5000" rows="4">{{ old('message') }}</textarea></label><button class="button">Send notification</button></form>
<div class="message-history">@forelse($messages as $message)<article class="message"><p class="preserve-lines">{{ $message->message }}</p><small>{{ $message->created_at->format('M j, Y · g:i A') }} · {{ $message->is_read ? 'Read' : 'Unread' }}</small></article>@empty<div class="empty"><strong>No messages sent yet</strong><p>Messages sent to this account will appear here.</p></div>@endforelse</div>
@include('admin.partials.pagination', ['paginator' => $messages])
@else<div class="empty tall">@include('admin.partials.icon', ['name' => 'message'])<strong>Select a recipient</strong><p>Choose an account to view sent messages or send a notification.</p></div>@endif
</section></div>
@endsection
