{{--
    Step 1 of the sign-up modal: "Sign up as..." Buyer, Seller, Logistics, Rider or Courier.
    Buyer / Seller / Logistics open the sign-up form on this page (js/auth/signup-roles.js);
    Rider and Courier go to the rider application with their courier type picked.
    Params: $chooseFirst (show this step first), $signupRole (the role the form starts on).
--}}
@php $signupRoles = \App\Support\SignupRoles::all(); @endphp
<div class="lm-step su-step-choose" data-su-step="choose" @unless($chooseFirst) hidden @endunless
     data-su-roles='@json(collect($signupRoles)->map(fn ($role) => ['label' => $role['label'], 'icon' => $role['icon']]))'>
    <div class="lm-head">
        <h2 class="lm-title" id="su-choose-title">Join cartzy</h2>
        <p class="lm-subtitle">Choose how you'll use cartzy to get started</p>
    </div>

    <p class="lm-roles-label">Sign up as...</p>
    <ul class="lm-role-list">
        @foreach($signupRoles as $key => $role)
            <li>
                @if(isset($role['url']))
                    <a href="{{ $role['url'] }}" class="lm-role">
                @else
                    <button type="button" class="lm-role" data-su-role="{{ $key }}">
                @endif
                    <span class="lm-role-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $role['icon'] !!}</svg>
                    </span>
                    <span class="lm-role-text">
                        <span class="lm-role-name">{{ $role['label'] }}</span>
                        <span class="lm-role-desc">{{ $role['description'] }}</span>
                    </span>
                @if(isset($role['url']))</a>@else</button>@endif
            </li>
        @endforeach
    </ul>

    <p class="lm-foot">Already have an account? <a href="{{ route('login') }}" class="lm-link">Log in</a></p>
</div>
