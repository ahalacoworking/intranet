<?php
/**
* Past Time Entity
*/
class PastTime extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'past_times';

    public function scopeRecap($query, $user, $start, $end)
    {
        $query->select(
                        'ressources.name',
                        DB::raw('HOUR(SEC_TO_TIME(SUM(TIME_TO_SEC(past_times.time_end) - TIME_TO_SEC(past_times.time_start)))) AS hours'),
                        DB::raw('MINUTE(SEC_TO_TIME(SUM(TIME_TO_SEC(past_times.time_end) - TIME_TO_SEC(past_times.time_start)))) AS minutes')
                    )
                    ->join('ressources', 'ressource_id', '=', 'ressources.id')
                    ->whereBetween('date_past', array($start, $end))
                    ->groupBy('ressource_id');
        if ($user) {
            $query->whereUserId($user);
        }

        return $query->get();
    }

    /**
     * Past Time belongs to User
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Past Time belongs to Ressource
     */
    public function ressource()
    {
        return $this->belongsTo('Ressource');
    }

    /**
     * Past time total
     */
    public function getPastTimeAttribute()
    {
        if ($this->time_end) {
            $date1 = new DateTime($this->time_end);
            $date2 = new DateTime($this->time_start);
            $diff = $date2->diff($date1);

            if ($diff->i) {
                return $diff->h.' heure(s) '.$diff->i.' minute(s)';
            } else {
                if ($diff->d) {
                    $diff->h = ($diff->d * 24);
                }
                return $diff->h.' heure(s)';
            }
        } else {
            return false;
        }
    }

    /**
     * Rules
     */
    public static $rules = array(
        'date_past' => 'required|min:1',
        'time_start' => 'min:5|max:5',
        'time_end' => 'min:5|max:5',
        'user_id' => 'required|exists:users,id',
        'ressource_id' => 'required|exists:ressources,id'
    );
}