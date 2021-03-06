<?php

class SubscriptionController extends BaseController
{
    public function cancelFilter()
    {
        Session::forget('filtre_subscription.user_id');
        Session::forget('filtre_subscription.organisation_id');
        Session::forget('filtre_subscription.city_id');
        return Redirect::route('subscription_list');
    }

    /**
     * List of vats
     */
    public function liste()
    {
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_city_id') && !empty(Input::get('filtre_city_id'))) {
                Session::put('filtre_subscription.city_id', Input::get('filtre_city_id'));
            } else {
                Session::forget('filtre_subscription.city_id');
            }
            if (Input::has('filtre_organisation_id')) {
                Session::put('filtre_subscription.organisation_id', Input::get('filtre_organisation_id'));
            } else {
                Session::forget('filtre_subscription.organisation_id');
            }
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_subscription.user_id', Input::get('filtre_user_id'));
            } else {
                Session::forget('filtre_subscription.user_id');
            }

        }

        $companies = array();
        $q = Subscription::join('organisations', 'subscription.organisation_id', '=', 'organisations.id')
            ->orderBy('organisations.name', 'desc')
            ->groupBy('organisations.name')
            ->addSelect('organisations.id')
            ->addSelect('organisations.name')
            ->addSelect(DB::raw('count(subscription.id) as count'))
            //->where('renew_at', '<=', date('Y-m-t'))
            ->where('renew_at', '<', (new DateTime())->modify('+1 month')->format('Y-m-d'))
            ->having('count', '>', 1);
        foreach ($q->get() as $item) {
            $companies[$item->id] = array('name' => $item->name, 'count' => $item->count);
        }


        $subscriptions = Subscription::orderBy('renew_at', 'ASC')
            ->join('users', 'subscription.user_id', '=', 'users.id')
            ->join('locations', 'users.default_location_id', '=', 'locations.id')
            ->select('subscription.*')//->join('cities', 'locations.city_id', '=', 'cities.id')
        ;
        if (Session::has('filtre_subscription.user_id')) {
            $subscriptions->where('subscription.user_id', '=', Session::get('filtre_subscription.user_id'));
        }
        if (Session::has('filtre_subscription.organisation_id')) {
            $subscriptions->where('subscription.organisation_id', '=', Session::get('filtre_subscription.organisation_id'));
        }
        if (Session::has('filtre_subscription.city_id')) {
            $subscriptions->where('locations.city_id', '=', Session::get('filtre_subscription.city_id'));
        }

        return View::make('subscription.liste', array('subscriptions' => $subscriptions->paginate(15), 'companies' => $companies));
    }

    public function add()
    {
        return View::make('subscription.add');
    }

    protected function populate($subscription)
    {
        $date_explode = explode('/', Input::get('renew_at'));
        $subscription->user_id = Input::get('user_id');
        $subscription->organisation_id = Input::get('organisation_id');
        $subscription->subscription_kind_id = Input::get('subscription_kind_id');
        $subscription->renew_at = $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];
        $subscription->is_automatic_renew_enabled = (bool)Input::get('is_automatic_renew_enabled');
        //$subscription->duration = Input::get('duration');
    }

    /**
     * Add Vat check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Subscription::$rulesAdd);
        if (!$validator->fails()) {
            $subscription = new Subscription;
            $this->populate($subscription);

            if ($subscription->save()) {
                return Redirect::route('subscription_list')->with('mSuccess', 'L\'abonnement a été ajouté');
            } else {
                return Redirect::route('subscription_add')->with('mError', 'Impossible de créer cet abonnement')->withInput();
            }
        } else {
            return Redirect::route('subscription_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    private function dataExist($id)
    {
        $data = Subscription::find($id);
        if (!$data) {
            return Redirect::route('subscription_list')->with('mError', 'Cet abonnement est introuvable !');
        } else {
            return $data;
        }
    }

    /**
     * Modify vat
     */
    public function modify($id)
    {
        $subscription = $this->dataExist($id);

        return View::make('subscription.add', array('subscription' => $subscription));
    }

    public function modify_check($id)
    {
        $validator = Validator::make(Input::all(), Subscription::$rulesAdd);
        if (!$validator->fails()) {
            $subscription = $this->dataExist($id);
            $this->populate($subscription);

            if ($subscription->save()) {
                return Redirect::route('subscription_list')->with('mSuccess', 'L\'abonnement a été mis à jour');
            } else {
                return Redirect::route('subscription_modify', $subscription->id)->with('mError', 'Impossible de modifier cet abonnement')->withInput();
            }
        } else {
            return Redirect::route('subscription_modify')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }


    public function delete($id)
    {
        if (Subscription::destroy($id)) {
            return Redirect::route('subscription_list')->with('mSuccess', 'Cet abonnement a bien été supprimé');
        } else {
            return Redirect::route('subscription_list')->with('mError', 'Impossible de supprimer cet abonnement');
        }
    }

    public function renew($id)
    {
        $subscription = $this->dataExist($id);
        $invoice = $subscription->renew();

        return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess',
            sprintf('La facture a été créée <a href="%s" class="btn btn-primary pull-right">Envoyer</a>', URL::route('invoice_send', $invoice->id)));
    }

    public function renewCompany($id)
    {
        $organisation = Organisation::find($id);
        if (!$organisation) {
            return Redirect::route('subscription_list')->with('mError', 'Société inconnue');
        }

        $subscriptions = Subscription::where('organisation_id', $id)
            ->join('subscription_kind', 'subscription_kind_id', '=', 'subscription_kind.id', 'left outer')
            ->orderBy('subscription_kind.price', 'DESC')
            ->orderBy('subscription.renew_at', 'ASC')
            //->where('subscription.renew_at', '<=', date('Y-m-t'))
            ->where('subscription.renew_at', '<', (new DateTime())->modify('+1 month')->format('Y-m-d'))
            ->select('subscription.*')
            ->get();
        if (count($subscriptions) == 0) {
            return Redirect::route('subscription_list')->with('mError', 'Aucun abonnement pour cette société');
        }
        $invoice = new Invoice();
        $invoice->type = 'F';
        $invoice->user_id = $organisation->accountant_id;
        $invoice->organisation_id = $organisation->id;

        if ($organisation->tva_number) {
            $invoice->details = sprintf('N° TVA Intracommunautaire: %s', $organisation->tva_number);
            $vat_types_id = VatType::whereValue(0)->first()->id;
        } else {
            $vat_types_id = VatType::whereValue(20)->first()->id;
        }

        $invoice->days = date('Ym');
        $invoice->date_invoice = date('Y-m-d');
        $invoice->number = Invoice::next_invoice_number($invoice->type, $invoice->days);
        $invoice->address = $organisation->fulladdress;

        $date = new DateTime($invoice->date_invoice);
        $date->modify('+1 month');
        $invoice->deadline = $date->format('Y-m-d');
        $invoice->expected_payment_at = $invoice->deadline;
        $invoice->save();

        $skipped_first = false;
        $discountable_amount = 0;
        $student_amount = 0;
        $index = 1;
        foreach ($subscriptions as $subscription) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = $subscription->kind->ressource_id;
            $invoice_line->amount = $subscription->kind->price;
            if ($subscription->kind->ressource_id == Ressource::TYPE_COWORKING) {
                if (!$skipped_first) {
                    $skipped_first = true;
                } else {
                    $discountable_amount += $invoice_line->amount;
                }
            }
            $date = new \DateTime($subscription->renew_at);
            $date2 = new \DateTime($subscription->renew_at);
            $invoice_line->subscription_from = $date->format('Y-m-d');
            $date2->modify('+' . $subscription->kind->duration);
            if ($subscription->kind->ressource_id == Ressource::TYPE_COWORKING) {
                $invoice_line->subscription_to = $date2->format('Y-m-d');
                $invoice_line->subscription_hours_quota = $subscription->kind->hours_quota;
                $invoice_line->subscription_user_id = $subscription->user_id;
                if ($subscription->user->is_student) {
                    if ($skipped_first) {
                        $student_amount += $invoice_line->amount - 0.2 * $invoice_line->amount;
                    } else {
                        $student_amount += $invoice_line->amount;
                    }
                }
            }


            $date2->modify('-1 day');
            $caption = str_replace(array('%OrganisationName%', '%UserName%'), array($subscription->organisation->name, $subscription->user->fullname), $subscription->kind->name);
            $invoice_line->text = sprintf("%s<br />\nDu %s au %s", $caption, $date->format('d/m/Y'), $date2->format('d/m/Y'));
            $invoice_line->vat_types_id = $vat_types_id;
            $invoice_line->order_index = $index++;
            $invoice_line->save();

            $date3 = new DateTime($subscription->renew_at);
            $date3->modify('+' . $subscription->kind->duration);
            $subscription->renew_at = $date3->format('Y-m-d');
            $subscription->save();
        }
        if ($discountable_amount > 0) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->amount = -0.2 * $discountable_amount;
            $invoice_line->text = 'Réduction commerciale équipe (-20% à partir du 2ème collaborateur)';
            $invoice_line->vat_types_id = $vat_types_id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->order_index = $index++;
            $invoice_line->save();
        }

        if ($student_amount > 0) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->amount = -0.2 * $student_amount;
            $invoice_line->text = 'Réduction commerciale étudiant (-20%)';
            $invoice_line->vat_types_id = $vat_types_id;
            $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
            $invoice_line->order_index = $index++;
            $invoice_line->save();
        }

        return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess',
            sprintf('La facture a été créée <a href="%s" class="btn btn-primary pull-right">Envoyer</a>', URL::route('invoice_send', $invoice->id)));

    }

    public function overuse()
    {
        $subscriptions = DB::select(DB::raw('SELECT 
invoices_items.id as invoices_items_id, invoices_items.subscription_overuse_managed,
if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as location, 
round(((sum(time_to_sec(timediff(time_end, time_start )) / 3600) / invoices_items.`subscription_hours_quota`) - 1) * 100) as overuse,
round((sum(time_to_sec(timediff(time_end, time_start )) / 3600) / invoices_items.`subscription_hours_quota`) * 100) as ratio,
invoices.date_invoice, concat(users.firstname, \' \', users.lastname) as username, users.id as user_id, sum(time_to_sec(timediff(time_end, time_start )) / 3600) as used, invoices_items.`subscription_hours_quota` as ordered
, invoices.id as invoice_id, invoices_items.`subscription_from`, invoices_items.`subscription_to`
from past_times join invoices on invoices.id = past_times.invoice_id
join invoices_items on invoices.id = invoices_items.invoice_id
join users on past_times.user_id = users.id
join locations on locations.id = users.default_location_id
join cities on cities.id = locations.city_id
where subscription_hours_quota > 0
and past_times.user_id = invoices_items.subscription_user_id
# and past_times.time_start > "2017-10-01"
and past_times.is_free = 0 
AND invoices_items.`subscription_from` != "0000-00-00 00:00:00"
AND invoices_items.`subscription_to` != "0000-00-00 00:00:00"
group by concat(invoices.id, "_", past_times.user_id)
having used > ordered
order by invoices_items.subscription_overuse_managed ASC, invoices_items.subscription_from DESC
'));
        foreach ($subscriptions as $index => $data) {
            $subscriptions[$index]->hours = floor($data->used);
            $subscriptions[$index]->minutes = round(($data->used - floor($data->used)) * 60);
        }

        //var_dump($subscriptions); exit;
        return View::make('subscription.overuse', array(
                'subscriptions' => $subscriptions
            )
        );


    }

    public function overuseManaged($id)
    {
        DB::statement(sprintf('UPDATE invoices_items SET subscription_overuse_managed = 1 WHERE id = %d', $id));
        return Redirect::route('subscription_overuse')
            ->with('mSuccess', 'Le dépassement a été noté comme traité');
    }

    public function manage()
    {
        $subscription = Subscription::where('user_id', '=', Auth::id())->first();
        $items = array();
        $options = SubscriptionKind::where('ressource_id', '=', Ressource::TYPE_COWORKING)->get();
        foreach ($options as $option) {
            $items[$option->id] = sprintf('%s (%d&euro;TTC/mois)', $option->shortName, $option->price * 1.2);
        }

        return View::make('subscription.manage', array(
                'subscription' => $subscription,
                'items' => $items,
            )
        );
    }

    public function manage_post()
    {
        $option_id = Input::get('option_id');

        $subscription = Subscription::where('user_id', '=', Auth::id())->first();

        $renew_enabled = (bool)Input::get('is_automatic_renew_enabled');
        if (!$renew_enabled) {
            if ($subscription) {
                $subscription->delete();
            }
            return Redirect::route('subscription_manage')
                ->with('mSuccess', 'Vos changements ont étés enregistrés');
        }

        if (!$option_id) {
            return Redirect::route('subscription_manage')
                ->with('mError', 'Merci de sélectionner la formule')->withInput();
        }

        if (!$subscription) {
            $subscription = new Subscription();
        }
        $date_explode = explode('/', Input::get('renew_at'));
        $renew_at = $date_explode[2] . '-' . $date_explode[1] . '-' . $date_explode[0];
        $subscription->user_id = Auth::id();
        if (!$subscription->organisation_id) {
            $organisation = Auth::user()->organisations->first();
            if (!$organisation) {
                $organisation = new Organisation();
                $user = Auth::user();
                $organisation->name = implode(' ', array($user->firstname, $user->lastname));
                $organisation->country_id = Country::where('code', 'FR')->first()->id;
                $organisation->save();
                $user->organisations()->save($organisation);
            }

            $subscription->organisation_id = $organisation->id;
        }
        $subscription->subscription_kind_id = $option_id;
        $subscription->is_automatic_renew_enabled = $renew_enabled;
        $subscription->renew_at = $renew_at;
        $subscription->save();

        if ($subscription->is_automatic_renew_enabled) {
            if ($renew_at <= date('Y-m-d')) {
                $invoice = $subscription->renew();
                $invoice->send();
                return Redirect::route('subscription_manage')
                    ->with('mSuccess', sprintf('Vos changements ont étés enregistrés, une nouvelle facture a été créée. Vous pouvez la <a href="%s">retrouver ici</a>.', URL::route('invoice_list')));
            }
        }

        return Redirect::route('subscription_manage')
            ->with('mSuccess', 'Vos changements ont étés enregistrés');
    }
}