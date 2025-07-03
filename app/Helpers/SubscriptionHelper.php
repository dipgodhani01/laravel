<?php

namespace App\Helpers;

use App\Models\PendingFees;
use App\Models\SubscriptionDetails;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Subscription;

class SubscriptionHelper
{
    public static function start_monthly_trial_subscription($customer_id, $user_id, $subscriptionPlan)
    {
        try {
            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');
            $date = date('Y-m-d 23:59:59');
            $trialDays = strtotime($date . '+' . $subscriptionPlan->trial_days . ' days');
            $subscriptionDetailsData = [
                'user_id' => $user_id,
                'stripe_subscription_id' => NULL,
                'stripe_subscription_shedule_id' => '',
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                'plan_amount' => $subscriptionPlan->plan_amount,
                'plan_amount_currency' => 'usd',
                'plan_interval' => 'month',
                'plan_interval_count' => 1,
                'plan_created_at' => date('Y-m-d H:i:s'),
                'plan_started_at' => $current_period_start,
                'plan_ended_at' => date('Y-m-d H:i:s', $trialDays),
                'trial_ended_at' => $trialDays,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $stripeData = SubscriptionDetails::updateOrCreate([
                'user_id' => $user_id,
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
            ], $subscriptionDetailsData);

            User::where('id', $user_id)->update(['is_subscribed' => 1]);

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function start_yearly_trial_subscription($customer_id, $user_id, $subscriptionPlan)
    {
        try {
            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');
            $date = date('Y-m-d 23:59:59');
            $trialDays = strtotime($date . '+' . $subscriptionPlan->trial_days . ' days');
            $subscriptionDetailsData = [
                'user_id' => $user_id,
                'stripe_subscription_id' => NULL,
                'stripe_subscription_shedule_id' => '',
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                'plan_amount' => $subscriptionPlan->plan_amount,
                'plan_amount_currency' => 'usd',
                'plan_interval' => 'year',
                'plan_interval_count' => 1,
                'plan_created_at' => date('Y-m-d H:i:s'),
                'plan_started_at' => $current_period_start,
                'plan_ended_at' => date('Y-m-d H:i:s', $trialDays),
                'trial_ended_at' => $trialDays,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $stripeData = SubscriptionDetails::updateOrCreate([
                'user_id' => $user_id,
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
            ], $subscriptionDetailsData);

            User::where('id', $user_id)->update(['is_subscribed' => 1]);

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function start_lifetime_trial_subscription($customer_id, $user_id, $subscriptionPlan)
    {
        try {
            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');
            $date = date('Y-m-d 23:59:59');
            $trialDays = strtotime($date . '+' . $subscriptionPlan->trial_days . ' days');
            $subscriptionDetailsData = [
                'user_id' => $user_id,
                'stripe_subscription_id' => NULL,
                'stripe_subscription_shedule_id' => '',
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                'plan_amount' => $subscriptionPlan->plan_amount,
                'plan_amount_currency' => 'usd',
                'plan_interval' => 'lifetime',
                'plan_interval_count' => 1,
                'plan_created_at' => date('Y-m-d H:i:s'),
                'plan_started_at' => $current_period_start,
                'plan_ended_at' => date('Y-m-d H:i:s', $trialDays),
                'trial_ended_at' => $trialDays,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $stripeData = SubscriptionDetails::updateOrCreate([
                'user_id' => $user_id,
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
            ], $subscriptionDetailsData);

            User::where('id', $user_id)->update(['is_subscribed' => 1]);

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function capture_monthly_pending_fees($customer_id, $user_id, $user_name, $subscriptionPlan, $stripe)
    {
        $totalAmount = $subscriptionPlan->plan_amount;
        $daysInMonth = date('t');
        $currentDay = date('j');
        $amountForRestDays = ceil(($daysInMonth - $currentDay) * ($totalAmount / $daysInMonth));

        $stripeChargeData = $stripe->charges->create([
            'amount' => $amountForRestDays * 100,
            'currency' => 'usd',
            'customer' => $customer_id,
            'description' => 'Monthly Pending Fees.',
            'shipping' => [
                'name' => $user_name,
                'address' => [
                    'line1' => '101, ABC complex',
                    'line2' => 'Varacha main road, Jakatnaka',
                    'city' => 'Surat',
                    'state' => 'Gujarat',
                    'postal_code' => '123456',
                    'country' => 'India'
                ]
            ]
        ]);

        if (!empty($stripeChargeData)) {
            $stripeCharge = $stripeChargeData->jsonSerialize();
            $chargeId = $stripeCharge['id'];
            $cusId = $stripeCharge['customer'];
            $pendingFeeData = [
                'user_id' => $user_id,
                'charge_id' => $chargeId,
                'customer_id' => $cusId,
                'amount' => $amountForRestDays,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            PendingFees::insert($pendingFeeData);
        }
    }

    public static function start_monthly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe)
    {
        try {
            $stripeData = null;
            $millisecondDate = strtotime(date('Y-m-') . '01');
            $current_period_start = date('Y-m-d', strtotime('+1 month', $millisecondDate)) . ' 00:00:00';
            $current_period_end = date('Y-m-t', strtotime('+1 month')) . ' 23:59:59';

            $stripeData = $stripe->subscriptions->create([
                'customer' => $customer_id,
                'items' => [
                    [
                        'price' => $subscriptionPlan->stripe_price_id,
                    ],
                ],
                'billing_cycle_anchor' => strtotime($current_period_start),
                'proration_behavior' => 'none',
            ]);

            $stripeData = $stripeData->jsonSerialize();
            if (!empty($stripeData)) {
                $subscriptionId = $stripeData['id'];
                $customerId = $stripeData['customer'];
                if (!empty($stripeData['items'])) {
                    $planId = $stripeData['items']['data'][0]['price']['id'];
                } else {
                    $planId = $stripeData['plan']['id'];
                }
                $priceData = $stripe->plans->retrieve(
                    $planId,
                    [],
                );
                $planAmount = ($priceData->amount / 100);
                $planCurrency = $priceData->currency;
                $planInterval = $priceData->interval;
                $planIntervalCount = $priceData->interval_count;
                $created = date('Y-m-d H:i:s', $stripeData['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_subscription_shedule_id' => '',
                    'stripe_customer_id' => $customerId,
                    'subscription_plan_price_id' => $planId,
                    'plan_amount' => $planAmount,
                    'plan_amount_currency' => $planCurrency,
                    'plan_interval' => $planInterval,
                    'plan_interval_count' => $planIntervalCount,
                    'plan_created_at' => $created,
                    'plan_started_at' => $current_period_start,
                    'plan_ended_at' => $current_period_end,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $stripeData = SubscriptionDetails::insert($subscriptionDetailsData);
                User::where('id', $user_id)->update(['is_subscribed' => 1]);
            }

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function capture_yearly_pending_fees($customer_id, $user_id, $user_name, $subscriptionPlan, $stripe)
    {
        $totalAmount = $subscriptionPlan->plan_amount;
        $monthInYear = 12;
        $currentMonth = date('m') - 1;
        $amountForRestMonth = ceil(($monthInYear - $currentMonth) * ($totalAmount / $monthInYear));

        $stripeChargeData = $stripe->charges->create([
            'amount' => $amountForRestMonth * 100,
            'currency' => 'usd',
            'customer' => $customer_id,
            'description' => 'Yearly Pending Fees.',
            'shipping' => [
                'name' => $user_name,
                'address' => [
                    'line1' => '101, ABC complex',
                    'line2' => 'Varacha main road, Jakatnaka',
                    'city' => 'Surat',
                    'state' => 'Gujarat',
                    'postal_code' => '123456',
                    'country' => 'India',
                ],
            ],
        ]);

        if (!empty($stripeChargeData)) {
            $stripeCharge = $stripeChargeData->jsonSerialize();
            $chargeId = $stripeCharge['id'];
            $cusId = $stripeCharge['customer'];
            $pendingFeeData = [
                'user_id' => $user_id,
                'charge_id' => $chargeId,
                'customer_id' => $cusId,
                'amount' => $amountForRestMonth,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            PendingFees::insert($pendingFeeData);
        }
    }

    public static function start_yearly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe)
    {
        try {
            $stripeData = null;
            $current_period_start = date('Y-', strtotime('+1 year')) . '01-01 00:00:00';
            $current_period_end = date('Y-', strtotime('+1 year')) . '12-31 23:59:59';

            $stripeData = $stripe->subscriptions->create([
                'customer' => $customer_id,
                'items' => [
                    [
                        'price' => $subscriptionPlan->stripe_price_id,
                    ],
                ],
                'billing_cycle_anchor' => strtotime($current_period_start),
                'proration_behavior' => 'none',
            ]);

            $stripeData = $stripeData->jsonSerialize();
            if (!empty($stripeData)) {
                $subscriptionId = $stripeData['id'];
                $customerId = $stripeData['customer'];
                if (!empty($stripeData['items'])) {
                    $planId = $stripeData['items']['data'][0]['price']['id'];
                } else {
                    $planId = $stripeData['plan']['id'];
                }
                $priceData = $stripe->plans->retrieve(
                    $planId,
                    [],
                );
                $planAmount = ($priceData->amount / 100);
                $planCurrency = $priceData->currency;
                $planInterval = $priceData->interval;
                $planIntervalCount = $priceData->interval_count;
                $created = date('Y-m-d H:i:s', $stripeData['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_subscription_shedule_id' => '',
                    'stripe_customer_id' => $customerId,
                    'subscription_plan_price_id' => $planId,
                    'plan_amount' => $planAmount,
                    'plan_amount_currency' => $planCurrency,
                    'plan_interval' => $planInterval,
                    'plan_interval_count' => $planIntervalCount,
                    'plan_created_at' => $created,
                    'plan_started_at' => $current_period_start,
                    'plan_ended_at' => $current_period_end,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $stripeData = SubscriptionDetails::insert($subscriptionDetailsData);
                User::where('id', $user_id)->update(['is_subscribed' => 1]);
            }

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function start_lifetime_subscription($customer_id, $user_id, $user_name, $subscriptionPlan, $stripe)
    {
        try {
            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');
            $current_period_end = '2099-' . date('m-d') . ' 23:59:59';


            $stripeChargeData = $stripe->charges->create([
                'amount' => $subscriptionPlan->plan_amount * 100,
                'currency' => 'usd',
                'customer' => $customer_id,
                'description' => 'Lifetime Pending Fees.',
                'shipping' => [
                    'name' => $user_name,
                    'address' => [
                        'line1' => '101, ABC complex',
                        'line2' => 'Varacha main road, Jakatnaka',
                        'city' => 'Surat',
                        'state' => 'Gujarat',
                        'postal_code' => '123456',
                        'country' => 'India',
                    ],
                ],
            ]);

            if (!empty($stripeChargeData)) {
                $stripeCharge = $stripeChargeData->jsonSerialize();

                $chargeId = $stripeCharge['id'];
                $cusId = $stripeCharge['customer'];
                $planCurrency = $stripeCharge['currency'];
                $created = date('Y-m-d H:i:s', $stripeCharge['created']);
                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $chargeId,
                    'stripe_subscription_shedule_id' => '',
                    'stripe_customer_id' => $cusId,
                    'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                    'plan_amount' => $subscriptionPlan->plan_amount,
                    'plan_amount_currency' => $planCurrency,
                    'plan_interval' => 'lifetime',
                    'plan_interval_count' => 1,
                    'plan_created_at' => $created,
                    'plan_started_at' => $current_period_start,
                    'plan_ended_at' => $current_period_end,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $stripeData = SubscriptionDetails::insert($subscriptionDetailsData);
                User::where('id', $user_id)->update(['is_subscribed' => 1]);
            }

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function cancle_current_subscription($user_id, $subscriptionDetail)
    {
        try {
            $secretKey = env('STRIPE_SECRET_KEY');
            Stripe::setApiKey($secretKey);

            if ($subscriptionDetail->stripe_subscription_id != null && $subscriptionDetail->stripe_subscription_id != '') {
                $subscription =  Subscription::retrieve($subscriptionDetail->stripe_subscription_id);
                Log::info($subscription);
                $subscription->cancel();
            }

            SubscriptionDetails::where('id', $subscriptionDetail->id)->update([
                'status' => 'cancelled',
                'cancle' => 1,
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);
            User::where('id', $user_id)->update(['is_subscribed' => 0]);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }

    public static function getCurrentSubscription()
    {
        $currentSubscription =  SubscriptionDetails::where([
            'user_id' => auth()->user()->id,
            'status' => 'active',
            'cancle' => 0,
        ])->orderBy('id', 'desc')->first();

        return $currentSubscription;
    }

    // below are the renew subscription methods
    public static function renew_monthly_subscription($subscriptionDetail, $user_id, $subscriptionPlan, $stripe)
    {
        try {
            $stripeData = null;
            $millisecondDate = strtotime(date('Y-m-') . '01');
            $current_period_start = date('Y-m-d', strtotime('+1 month', $millisecondDate)) . ' 00:00:00';
            $current_period_end = date('Y-m-t', strtotime('+1 month')) . ' 23:59:59';

            $stripeData = $stripe->subscriptions->create([
                'customer' => $subscriptionDetail->stripe_customer_id,
                'items' => [
                    [
                        'price' => $subscriptionPlan->stripe_price_id,
                    ],
                ],
                'billing_cycle_anchor' => strtotime($current_period_start),
                'proration_behavior' => 'none',
            ]);

            $stripeData = $stripeData->jsonSerialize();
            if (!empty($stripeData)) {
                $subscriptionId = $stripeData['id'];
                $customerId = $stripeData['customer'];
                if (!empty($stripeData['items'])) {
                    $planId = $stripeData['items']['data'][0]['price']['id'];
                } else {
                    $planId = $stripeData['plan']['id'];
                }
                $priceData = $stripe->plans->retrieve(
                    $planId,
                    [],
                );
                $planAmount = ($priceData->amount / 100);
                $planCurrency = $priceData->currency;
                $planInterval = $priceData->interval;
                $planIntervalCount = $priceData->interval_count;
                $created = date('Y-m-d H:i:s', $stripeData['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_subscription_shedule_id' => '',
                    'stripe_customer_id' => $customerId,
                    'subscription_plan_price_id' => $planId,
                    'plan_amount' => $planAmount,
                    'plan_amount_currency' => $planCurrency,
                    'plan_interval' => $planInterval,
                    'plan_interval_count' => $planIntervalCount,
                    'plan_created_at' => $created,
                    'plan_started_at' => $current_period_start,
                    'plan_ended_at' => $current_period_end,
                    'trial_ended_at' => NULL,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $stripeData = SubscriptionDetails::where('id', $subscriptionDetail->id)->update($subscriptionDetailsData);
                User::where('id', $user_id)->update(['is_subscribed' => 1]);
            }

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function renew_yearly_subscription($subscriptionDetail, $user_id, $subscriptionPlan, $stripe)
    {
        try {
            $stripeData = null;
            $current_period_start = date('Y-', strtotime('+1 year')) . '01-01 00:00:00';
            $current_period_end = date('Y-', strtotime('+1 year')) . '12-31 23:59:59';

            $stripeData = $stripe->subscriptions->create([
                'customer' => $subscriptionDetail->stripe_customer_id,
                'items' => [
                    [
                        'price' => $subscriptionPlan->stripe_price_id,
                    ],
                ],
                'billing_cycle_anchor' => strtotime($current_period_start),
                'proration_behavior' => 'none',
            ]);

            $stripeData = $stripeData->jsonSerialize();
            if (!empty($stripeData)) {
                $subscriptionId = $stripeData['id'];
                $customerId = $stripeData['customer'];
                if (!empty($stripeData['items'])) {
                    $planId = $stripeData['items']['data'][0]['price']['id'];
                } else {
                    $planId = $stripeData['plan']['id'];
                }
                $priceData = $stripe->plans->retrieve(
                    $planId,
                    [],
                );
                $planAmount = ($priceData->amount / 100);
                $planCurrency = $priceData->currency;
                $planInterval = $priceData->interval;
                $planIntervalCount = $priceData->interval_count;
                $created = date('Y-m-d H:i:s', $stripeData['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_subscription_shedule_id' => '',
                    'stripe_customer_id' => $customerId,
                    'subscription_plan_price_id' => $planId,
                    'plan_amount' => $planAmount,
                    'plan_amount_currency' => $planCurrency,
                    'plan_interval' => $planInterval,
                    'plan_interval_count' => $planIntervalCount,
                    'plan_created_at' => $created,
                    'plan_started_at' => $current_period_start,
                    'plan_ended_at' => $current_period_end,
                    'trial_ended_at' => NULL,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $stripeData = SubscriptionDetails::where('id', $subscriptionDetail->id)->insert($subscriptionDetailsData);
                User::where('id', $user_id)->update(['is_subscribed' => 1]);
            }

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function renew_lifetime_subscription($subscriptionDetail, $user_id, $user_name, $subscriptionPlan, $stripe)
    {
        try {
            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');
            $current_period_end = '2099-' . date('m-d') . ' 23:59:59';

            $stripeChargeData = $stripe->charges->create([
                'amount' => $subscriptionPlan->plan_amount * 100,
                'currency' => 'usd',
                'customer' => $subscriptionDetail->stripe_customer_id,
                'description' => 'Lifetime Pending Fees.',
                'shipping' => [
                    'name' => $user_name,
                    'address' => [
                        'line1' => '101, ABC complex',
                        'line2' => 'Varacha main road, Jakatnaka',
                        'city' => 'Surat',
                        'state' => 'Gujarat',
                        'postal_code' => '123456',
                        'country' => 'India',
                    ],
                ],
            ]);

            if (!empty($stripeChargeData)) {
                $stripeCharge = $stripeChargeData->jsonSerialize();

                $chargeId = $stripeCharge['id'];
                $cusId = $stripeCharge['customer'];
                $planCurrency = $stripeCharge['currency'];
                $created = date('Y-m-d H:i:s', $stripeCharge['created']);
                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $chargeId,
                    'stripe_subscription_shedule_id' => '',
                    'stripe_customer_id' => $cusId,
                    'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                    'plan_amount' => $subscriptionPlan->plan_amount,
                    'plan_amount_currency' => $planCurrency,
                    'plan_interval' => 'lifetime',
                    'plan_interval_count' => 1,
                    'plan_created_at' => $created,
                    'plan_started_at' => $current_period_start,
                    'plan_ended_at' => $current_period_end,
                    'trial_ended_at' => NULL,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $stripeData = SubscriptionDetails::where('id', $subscriptionDetail->id)->insert($subscriptionDetailsData);
                User::where('id', $user_id)->update(['is_subscribed' => 1]);
            }

            return $stripeData;
        } catch (\Exception $e) {
            return null;
        }
    }
}
