<form class="filters" method="GET">
<label class="search-field"><span>Search</span><input name="search" value="{{ request('search') }}" placeholder="Name or email"></label>
<label><span>Role</span><select name="role"><option value="">All roles</option>@foreach(['buyer', 'seller', 'courier', 'logistics', 'admin'] as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>@endforeach</select></label>
<label><span>Status</span><select name="status"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
<button class="button">Apply filters</button><a class="button subtle" href="{{ url()->current() }}">Reset</a>
</form>
