<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\SubscriptionHelper;
use App\Models\SubscriptionDetails;
use App\Models\SubscriptionPlan;
use Stripe\Stripe;
use Stripe\StripeClient;

class UpdateTrialSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-trial-subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update trial use subscription into real subscription';

    protected $STRIPE_SECRET_KEY;

    public function __construct()
    {
        $this->STRIPE_SECRET_KEY = env('STRIPE_SECRET_KEY');
        parent::__construct();
    }

    public function handle()
    {
        $secretKey = $this->STRIPE_SECRET_KEY;
        Stripe::setApiKey($secretKey);
        $stripe = new StripeClient($secretKey);

        $subscriptionDetails = SubscriptionDetails::with('user',)->where(['status' => 'active', 'cancle' => 0])->where('plan_ended_at', '<', date('Y-m-d H:i:s'))->whereNotNull('trial_ended_at')->orderBy('id', 'desc')->get();
        if (count($subscriptionDetails) > 0) {
            foreach ($subscriptionDetails as $detail) {
                $subscriptionPlan =  SubscriptionPlan::where('stripe_price_id', $detail->subscription_plan_price_id)->first();
                if ($detail->plan_interval == 'month') {
                    SubscriptionHelper::capture_monthly_pending_fees($detail->stripe_customer_id, $detail->user_id,  $detail->user->name, $subscriptionPlan, $stripe);
                    SubscriptionHelper::renew_monthly_subscription($detail,  $detail->user_id, $subscriptionPlan, $stripe);
                } else if ($detail->plan_interval == 'year') {
                    SubscriptionHelper::capture_yearly_pending_fees($detail->stripe_customer_id, $detail->user_id,  $detail->user->name, $subscriptionPlan, $stripe);
                    SubscriptionHelper::renew_yearly_subscription($detail,  $detail->user_id, $subscriptionPlan, $stripe);
                } else if ($detail->plan_interval == 'lifetime') {
                    SubscriptionHelper::renew_lifetime_subscription($detail,  $detail->user_id, $detail->user->name, $subscriptionPlan, $stripe);
                }
            }
        }
    }
}
