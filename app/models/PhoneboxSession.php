<?php

/**
 * PhoneBox Entity
 */
class PhoneboxSession extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'phonebox_session';

    /**
     * PhoneBox belongs to User
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'start_at' => 'required',
        'user_id' => 'required',
    );

}