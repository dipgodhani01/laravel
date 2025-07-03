@extends('layouts.layout')

@section('content')
<!-- Subscription Plan -->
<div class="h-[calc(100vh-136px)] mt-[68px]">
    <div class="container mx-auto p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">

            @php
            $currentPlan = app('subscription_helper')->getCurrentSubscription();

            @endphp

            @foreach($plans as $plan)
            <div class="bg-gray-200 px-2 py-4">
                <h3 class="text-xl text-blue-800 font-semibold text-center">
                    {{$plan->name}}
                    @if($currentPlan && $currentPlan->subscription_plan_price_id == $plan->stripe_price_id)
                    <span class="text-green-600 text-sm font-normal"> (Active)</span>
                    @endif
                </h3>
                <div class="text-center">
                    <span class="font-semibold">$ {{$plan->plan_amount}}.00</span>
                </div>
                <div class="text-center mt-4">
                    @if($currentPlan)
                    @if($currentPlan->subscription_plan_price_id == $plan->stripe_price_id)
                    @if($currentPlan->plan_interval == 'lifetime')
                    <button class="p-2 bg-blue-600 text-white text-sm font-medium rounded">
                        Subscribed
                    </button>
                    @else
                    <button class="p-2 bg-red-600 text-white text-sm font-medium rounded subscriptionCancel">
                        Cancel Plan
                    </button>
                    @endif
                    @else
                    @if($currentPlan->plan_interval == 'lifetime')
                    <button class="p-2 bg-gray-400 text-white text-sm font-medium rounded" disabled>
                        Already Subscribed (Lifetime)
                    </button>
                    @else
                    <button class="p-2 bg-blue-700 text-white text-sm font-medium rounded confirmationBtn"
                        data-id="{{ $plan->id }}">
                        Subscribe
                    </button>
                    @endif
                    @endif
                    @else
                    <button class="p-2 bg-blue-700 text-white text-sm font-medium rounded confirmationBtn"
                        data-id="{{ $plan->id }}">
                        Subscribe
                    </button>
                    @endif
                </div>

            </div>
            @endforeach
        </div>
    </div>
</div>


<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md">
        <div class="flex justify-between items-center border-b p-4">
            <h3 class="text-xl font-semibold text-green-700" id="modelTitle"></h3>
            <button onclick="closeModal('confirmationModal')"
                class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div class="p-4">
            <div class="confirmation-data">
                <div class="loader mx-auto"></div>
            </div>
        </div>
        <div class="flex justify-end space-x-3 border-t p-4">
            <button onclick="closeModal('confirmationModal')"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                Cancel
            </button>
            <button onclick="proceedToStripe()" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800">
                Proceed to Payment
            </button>
        </div>
    </div>
</div>

<!-- Stripe Payment Modal -->
<div id="stripeModel" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md">
        <div class="flex justify-between items-center border-b p-4">
            <h3 class="text-xl font-semibold text-green-700">Stripe Payment</h3>
            <button onclick="closeModal('stripeModel')"
                class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div class="p-4">
            <input type="hidden" name="planId" id="planId">
            <div id="card-element" class="border p-2 mb-3"></div>
            <div id="card-errors" class="card-errors text-red-600"></div>

        </div>
        <div class="flex justify-end space-x-3 border-t p-4">
            <button onclick="closeModal('stripeModel')"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                Cancel
            </button>
            <button type="submit" id="buyPlanSubmitBtn"
                class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800">
                Pay Now
            </button>
        </div>
    </div>
</div>


@endsection

@push('scripts')

<script src="https://js.stripe.com/v3/"></script>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.getElementById(modalId).classList.add('flex');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.getElementById(modalId).classList.remove('flex');
    }

    function proceedToStripe() {
        closeModal('confirmationModal');
        openModal('stripeModel');
    }
</script>

<script>
    $(document).ready(function() {
        $(".confirmationBtn").click(function() {
            $('#modelTitle').html(`<div class="loader-sm mx-auto"></div>`);
            $('.confirmation-data').html(`<div class="loader mx-auto"></div>`);
            const planId = $(this).data('id');

            $('#planId').val(planId);
            openModal('confirmationModal');

            $.ajax({
                type: "POST",
                url: "{{ route('getPlanDetails') }}",
                data: {
                    id: planId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#modelTitle').text(`${data.name} ($${data.plan_amount})`);
                        $('.confirmation-data').html(
                            `<p class="text-center text-pink-800 font-medium">${response.msg}</p>`
                        );
                    } else {
                        alert("Something went wrong!");
                    }
                },
                error: function() {
                    alert("Server Error!");
                }
            });
        });


        // subscription cancel
        $('.subscriptionCancel').click(function() {
            let obj = $(this);
            $(obj).html(`<div class="loader-sm"></div>`)
            $(obj).attr(`disabled`, `disabled`)
            const plan_id = $("#planId").val();
            $.ajax({
                url: "{{ route('cancelSubscription') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(res) {
                    if (res.success) {
                        alert(res.msg);
                        window.location.reload();
                    } else {
                        alert("Something went wrong!");
                        $(obj).html(`Cancel Plan`)
                        $(obj).removeAttr(`disabled`)
                    }
                },

            });
        })

    });

    // Stripe code start

    // Stripe.js loaded
    let submitButton = document.getElementById('buyPlanSubmitBtn');
    if (window.Stripe) {
        const stripe = Stripe("{{ env('STRIPE_PUBLIC_KEY') }}");
        const elements = stripe.elements();

        const card = elements.create('card', {
            hidePostalCode: true,

        });
        card.mount('#card-element');

        card.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        submitButton.addEventListener('click', function(e) {
            e.preventDefault();

            submitButton.disabled = true;
            submitButton.innerHTML = ` <div class="loader-sm"> </div>`;
            stripe.createToken(card).then((res) => {
                if (res.error) {
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = res.error.message;
                    submitButton.disabled = false;
                    submitButton.innerHTML = `Pay Now`;
                } else {
                    createSubscription(res.token);
                }
            });
        });
    }

    function createSubscription(token) {
        const plan_id = $("#planId").val();
        $.ajax({
            url: "{{ route('createSubscription') }}",
            type: "POST",
            data: {
                plan_id,
                data: token, // Send token object
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    alert(res.msg);
                    closeModal('stripeModel');
                    submitButton.disabled = false;
                    submitButton.innerHTML = `Pay Now`;
                    location.reload();
                } else {
                    alert("Something went wrong!");
                    submitButton.disabled = false;
                    submitButton.innerHTML = `Pay Now`;
                }
            },

        });

    }
</script>
@endpush