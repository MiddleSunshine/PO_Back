<?php

class OKRItem extends Base{
    public static $table="OKR_Item";

    const WEEK_STATUS_SUCCESS='success';
    const WEEK_STATUS_FAIL='fail';

    const STATUS_GIVE_UP='give_up';
    const STATUS_FINISHED='finished';
    const STATUS_PROCESSING='processing';
    const STATUS_INIT='init';

    const TYPE_WEEK='week';
    const TYPE_MONTH='month';
}