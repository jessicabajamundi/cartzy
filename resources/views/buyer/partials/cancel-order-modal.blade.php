<dialog id="cancelOrderDialog" aria-labelledby="cancelOrderTitle" aria-describedby="cancelOrderDescription" class="m-auto w-full max-w-lg rounded-3xl border border-[#E1DDE7] bg-white p-6 text-gray-900 shadow-xl" style="max-height:90vh;width:calc(100% - 2rem)">
    <form method="POST" id="cancelOrderForm" class="space-y-4">
        @csrf
        <h2 id="cancelOrderTitle" class="text-xl font-bold">Cancel order</h2>
        <p id="cancelOrderReference" class="text-sm font-semibold text-gray-500"></p>
        <p id="cancelOrderDescription" class="text-sm text-gray-600">Please select a reason for cancelling your order.</p>
        <fieldset class="space-y-2">
            <legend class="sr-only">Cancellation reason (required)</legend>
            @foreach(\App\Models\SellerOrder::CANCELLATION_REASONS as $reason)
                <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-3 text-sm cursor-pointer">
                    <input type="radio" name="reason" value="{{ $reason }}" required class="mt-1 accent-[#564B68]">
                    <span>{{ $reason }}</span>
                </label>
            @endforeach
        </fieldset>
        <div id="cancelOrderDetails" hidden>
            <label for="cancelReasonDetails" class="block text-sm font-semibold">Please specify your reason.</label>
            <textarea id="cancelReasonDetails" name="reason_details" maxlength="1000" rows="3" disabled class="mt-2 w-full rounded-xl border border-gray-300 p-3 text-sm" placeholder="Please specify your reason."></textarea>
        </div>
        <p class="text-sm font-semibold">Are you sure you want to cancel this order?</p>
        <p class="text-xs text-gray-500">If you have already paid, please contact the seller to arrange your refund.</p>
        <div class="flex flex-wrap justify-end gap-2">
            <button type="button" id="keepOrderButton" class="{{ $btnGhost }}">Keep order</button>
            <button type="submit" id="confirmCancellationButton" class="{{ $btnPrimary }} disabled:opacity-50 disabled:cursor-not-allowed" disabled>Confirm cancellation</button>
        </div>
    </form>
</dialog>
<style>#cancelOrderDialog::backdrop { background: rgb(17 24 39 / 45%); }</style>
<script src="{{ asset('js/buyer/cancel-order.js') }}" defer></script>
