<?php

namespace App\Interfaces;

interface ProviderInterface {
    public static function get_id();

    public static function get_table_id_field();

    public static function read_local_transactions();
}
