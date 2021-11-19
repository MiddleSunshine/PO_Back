<?php

class OKRDecision extends Base{
    public static $table="OKR_Decision";

    const STATUS_PROCESSING='processing';
    const STATUS_FINISHED='finished';
    const STATUS_GIVE_UP='give_up';
}