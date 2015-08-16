<?php


$cacheKey = 'birthday';
$cacheContent = Cache::get($cacheKey);
if (empty($cacheContent)) {
    $users = User::where('birthday', '<>', '0000-00-00')
            ->whereRaw('DATE_ADD(birthday,
                INTERVAL YEAR(CURDATE())-YEAR(birthday)
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(birthday),1,0)
                YEAR)
            BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)')
            ->whereIsMember(true)
            ->orderByRaw('DATE_ADD(birthday,
                INTERVAL YEAR(CURDATE())-YEAR(birthday)
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(birthday),1,0)
                YEAR) ASC')
            ->limit(5)->get();
    if (count($users) > 0) {
        $cacheContent = View::make('partials.next_birthday_inner', array('users' => $users))->render();
        Cache::put($cacheKey, $cacheContent, new \dateTime(date('Y-m-d 00:00:00', strtotime('tomorrow'))));
    }
}
echo $cacheContent;
?>
