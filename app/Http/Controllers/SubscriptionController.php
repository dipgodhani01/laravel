<?php

namespace App\Http\Controllers;

use App\Helpers\SubscriptionHelper;
use App\Models\CardDetail;
use App\Models\SubscriptionDetails;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function loadSubscription()
    {
        $plans = SubscriptionPlan::where('enabled', 1)->get();
        return view('subscription', compact('plans'));
    }

    public function getPlanDetails(Request $request)
    {
        try {
            $planData = SubscriptionPlan::where('id', $request->id)->first();
            $haveAnyActivePlan = SubscriptionDetails::where(['user_id' => auth()->user()->id, 'status' => 'active'])->count();
            $msg = '';
            if ($haveAnyActivePlan == 0 && ($planData->trial_days !== null && $planData->trial_days !== "")) {
                $msg = 'You will get' . ' ' . $planData->trial_days . ' ' . "days free trial, and after we will charge $" . $planData->plan_amount . ' ' . "for" . ' ' . $planData->name . ' ' . "Subscription plan";
            } else {
                $msg = "We will charge $" . $planData->plan_amount . ' ' . "for" . ' ' . $planData->name . ' ' . "Subscription plan";
            }
            return response()->json(["success" => true, 'msg' => $msg, 'data' => $planData]);
        } catch (\Exception $e) {
            return response()->json(["success" => false]);
        }
    }

    public function createSubscription(Request $request)
    {
        try {
            $user_id = auth()->user()->id;
            $user_name = auth()->user()->name;
            $secretKey = env('STRIPE_SECRET_KEY');

            Stripe::setApiKey($secretKey);

            $stripeData = $request->input('data');

            $stripe = new StripeClient($secretKey);

            $customer = $this->createCustomer($stripeData['id']);
            $customer_id = $customer->id;

            $subscriptionPlan = SubscriptionPlan::where('id', $request->plan_id)->first();

            // Start and change subscription conditions START

            // check if exists any current active subscription
            $subscriptionDetail =  SubscriptionDetails::where(['user_id' => $user_id, 'status' => 'active', 'cancle' => 0])->orderBy('id', 'desc')->first();

            // check if exists any subscription available of the user
            $subscriptionDetailsCount =  SubscriptionDetails::where(['user_id' => $user_id])->orderBy('id', 'desc')->count();

            // if monthly available & change into yearly
            if ($subscriptionDetail && $subscriptionDetail->plan_interval == 'month' && $subscriptionPlan->type == 1) {
            }

            // if monthly available & change into lifetime
            else if ($subscriptionDetail && $subscriptionDetail->plan_interval == 'month' && $subscriptionPlan->type == 2) {
            }

            // if yearly available & change into monthly
            else if ($subscriptionDetail && $subscriptionDetail->plan_interval == 'year' && $subscriptionPlan->type == 0) {
            }

            // if yearly available & change into lifetime
            else if ($subscriptionDetail && $subscriptionDetail->plan_interval == 'year' && $subscriptionPlan->type == 2) {
            }

            // not available any plan already
            else {
                if ($subscriptionDetailsCount == 0) {
                    // new user
                    if ($subscriptionPlan->type == 0) { // monthly trial
                        $subscriptionData =  SubscriptionHelper::start_monthly_trial_subscription($customer_id, $user_id, $subscriptionPlan);
                    } else if ($subscriptionPlan->type == 1) { // yearly trial
                        $subscriptionData =  SubscriptionHelper::start_yearly_trial_subscription($customer_id, $user_id, $subscriptionPlan);
                    } else if ($subscriptionPlan->type == 2) { // lifetime trial
                        $subscriptionData =  SubscriptionHelper::start_lifetime_trial_subscription($customer_id, $user_id, $subscriptionPlan);
                    }
                } else {
                    // user all subscription cancelled
                    if ($subscriptionPlan->type == 0) {
                        // monthly subscription
                        SubscriptionHelper::capture_monthly_pending_fees($customer_id, $user_id, $user_name, $subscriptionPlan, $stripe);
                        $subscriptionData =  SubscriptionHelper::start_monthly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe);
                    } else if ($subscriptionPlan->type == 1) {
                        // yearly subscription
                    } else if ($subscriptionPlan->type == 2) {
                        // lifetime subscription
                    }
                }
            }

            // Start and change subscription conditions END

            $this->saveCardDetails($stripeData, $user_id, $customer_id);
            if ($subscriptionData) {
                return response()->json(['success' => true, 'msg' => 'Subscription purchased!']);
            } else {
                return response()->json(['success' => false, 'msg' => 'Subscription purchased failed!']);
            }
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "msg" => $e->getMessage(),
            ]);
        }
    }

    public function createCustomer($token_id)
    {
        $customer = Customer::create([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'source' => $token_id,
        ]);
        return $customer;
    }

    public function saveCardDetails($cardData, $user_id, $customer_id)
    {
        Log::info($cardData);
        Log::info($user_id);
        Log::info($customer_id);
        CardDetail::updateOrCreate([
            'user_id' => $user_id,
            'card_number' => $cardData['card']['last4']
        ], [
            'user_id' => $user_id,
            'customer_id' => $customer_id,
            'card_id' => $cardData['card']['id'],
            'name' => $cardData['card']['name'],
            'card_number' => $cardData['card']['last4'],
            'brand' => $cardData['card']['brand'],
            'month' => $cardData['card']['exp_month'],
            'year' => $cardData['card']['exp_year'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}