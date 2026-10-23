@php($notificationData = \App\Services\SellerNotifications::feed(auth()->user()))
<a class="nav-link seller-notifications-link {{ request()->routeIs('seller.notifications') ? 'active' : '' }}" href="{{ route('seller.notifications', [], false) }}" data-notifications-url="{{ route('seller.notifications', [], false) }}" @if(request()->routeIs('seller.notifications')) aria-current="page" @endif>
    @include('seller.partials.icon', ['name' => 'bell'])<span>Notifications</span><span class="badge" data-notification-count aria-live="polite" @if($notificationData['notificationCount'] <= 0) hidden @endif>{{ $notificationData['notificationCount'] > 0 ? $notificationData['notificationCount'] : '' }}</span>
</a>
