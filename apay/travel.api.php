<?php

class travelData
{
    public static function getAllByFilter($limit = 500, $options = array())
    {
        $addQuery = "WHERE ";
        if (count($options) > 0) {
            foreach ($options as $key => $value) {
                if ($key == "status" && $value != "all") {
                    $addQuery .= " travel_status = '{$value}' AND";
                } else if ($key == "regex" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                } else if ($key == "regexOperator" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                } else if ($key == "regexName" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                } else if ($key == "regexPhone" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                } else if ($key == "regexEmail" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                }
            }
            if ($options["status"] == "all" && $options["regex"]["status"] == "all" && $options["regexOperator"]["status"] == "all" && $options["regexName"]["status"] == "all" && $options["regexPhone"]["status"] == "all" && $options["regexEmail"]["status"] == "all") {
                $addQuery = "";
            } else {
                $addQuery = rtrim($addQuery, "AND");
            }
        } else {
            $addQuery = "";
        }

        $limit = "LIMIT {$limit}";
        return getwayConnect::getwayData("SELECT data_travel.*,data_source.source_name,regions.country,data_sellpoint.sell_name FROM data_travel JOIN data_source ON data_travel.travel_source = data_source.id JOIN regions ON regions.id = data_travel.travel_country JOIN data_sellpoint ON data_sellpoint.id = data_travel.travel_sellpoint  {$addQuery} ORDER BY data_travel.travel_date DESC {$limit}", PDO::FETCH_ASSOC);
    }

    public static function getCountByFilter($options = array())
    {
        $addQuery = "WHERE ";
        if (count($options) > 0) {
            foreach ($options as $key => $value) {
                if ($key == "status" && $value != "all") {
                    $addQuery .= " travel_status = '{$value}' AND";
                } else if ($key == "regex" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                } else if ($key == "regexOperator" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                } else if ($key == "regexName" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                } else if ($key == "regexPhone" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                } else if ($key == "regexEmail" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " REGEXP '" . $value["exp"] . "' AND";
                }
            }
            if ($options["status"] == "all" && $options["regex"]["status"] == "all" && $options["regexOperator"]["status"] == "all" && $options["regexName"]["status"] == "all" && $options["regexPhone"]["status"] == "all" && $options["regexEmail"]["status"] == "all") {
                $addQuery = "";
            } else {
                $addQuery = rtrim($addQuery, "AND");
            }
        } else {
            $addQuery = "";
        }
        return getwayConnect::getwayCount("SELECT * FROM data_travel {$addQuery}");
    }

    public static function getBaseDataById($id)
    {
        //$data2 = getwayConnect::getwayData("SELECT `data_travel`.*,`data_hotel_booking`.`hotel_id`, `data_hotel_booking`.`guests`, `data_hotel_booking`.`adult_count`, `data_hotel_booking`.`adult_price`, `data_hotel_booking`.`child_count`, `data_hotel_booking`.`child_price`, `data_hotel_booking`.`check_in`, `data_hotel_booking`.`check_out` FROM `data_travel` RIGHT JOIN `data_hotel_booking` ON `data_travel`.`hotel_booking_id` =  `data_hotel_booking`.`id` WHERE `data_travel`.`id` = '{$id}'",PDO::FETCH_ASSOC);
        //if($data2){
        //return $data2;
        //}else{
        $data = getwayConnect::getwayData("SELECT * FROM `data_travel` WHERE `id` = '{$id}'", PDO::FETCH_ASSOC);
        return $data;
        //}
    }


    public static function getOperators()
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM user WHERE user_level > 79 AND user_level < 90", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["uid"] . "\">" . $data[$k]["username"] . "</option>";
        }
        return $select;
    }

    public static function getServices()
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM data_service", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["id"] . "\">" . $data[$k]["service_name"] . "</option>";
        }
        return $select;
    }

    public static function getSource()
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM data_source", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["id"] . "\">" . $data[$k]["source_name"] . "</option>";
        }
        return $select;
    }

    public static function getSellPoint()
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM data_sellpoint", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["id"] . "\">" . $data[$k]["sell_name"] . "</option>";
        }
        return $select;
    }

    public static function getCurrency()
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM currency ORDER BY currency_order ASC", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["currency_id"] . "\">" . $data[$k]["currency_code"] . "</option>";
        }
        return $select;
    }

    public static function getRegions()
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM regions ORDER BY country", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["id"] . "\">" . $data[$k]["country"] . "</option>";
        }
        return $select;
    }

    public static function getStatuses()
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM data_status", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["id"] . "\">" . $data[$k]["status_name"] . "</option>";
        }
        return $select;
    }

    public static function getPayment()
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM data_payment", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["id"] . "\">" . $data[$k]["payment_name"] . "</option>";
        }
        return $select;
    }

    public static function getSubRegionById($id)
    {
        $select = "";
        $data = getwayConnect::getwayData("SELECT * FROM subregions WHERE region_id = '{$id}' ORDER BY name", PDO::FETCH_ASSOC);
        for ($k = 0; $k < count($data); $k++) {
            $select .= "<option value=\"" . $data[$k]["id"] . "\">" . $data[$k]["name"] . "</option>";
        }
        if (empty($data)) {
            $select = "<option value=\"123456789\">none</option>";
        }
        return $select;
    }

    public static function addLog($uid, $tid, $status = "added", $message = '')
    {
//			$log = $message.' '.$status." on:".getway::utc();
        $log = $message . ' ' . $status . " on:" . date('Y-m-d H:i:s');

        return getwayConnect::getwaySend("INSERT INTO data_travel_log SET user_uid='{$uid}', travel_log='{$log}',travel_id = '{$tid}'");
    }

    public static function getLastLog($travelUniq)
    {
        return getwayConnect::getwayData("SELECT data_travel_log.*,user.username FROM data_travel_log JOIN user ON user.uid = data_travel_log.user_uid WHERE travel_id = '{$travelUniq}' ORDER BY travel_log DESC LIMIT 1", PDO::FETCH_ASSOC);
    }
 public static function getAllLog($travelUniq)
    {
        return getwayConnect::getwayData("SELECT data_travel_log.*,user.username FROM data_travel_log JOIN user ON user.uid = data_travel_log.user_uid WHERE travel_id = '{$travelUniq}' ORDER BY update_date DESC", PDO::FETCH_ASSOC);
    }

    public static function getAddedLog($travelUniq)
    {
        return getwayConnect::getwayData("SELECT data_travel_log.*,user.username FROM data_travel_log JOIN user ON user.uid = data_travel_log.user_uid WHERE data_travel_log.travel_id = '{$travelUniq}' AND travel_log REGEXP 'added'", PDO::FETCH_ASSOC);
    }

    public static function insertHotelRooms($query_hotels, $data_hotels)
    {
        //query room
        $query_rooms = false;

        if ($data_hotels) {

            $hotel_rooms = base64_decode($data_hotels);

            if ($hotel_rooms) {
                $hotel_rooms = json_decode($hotel_rooms, true);

                if ($hotel_rooms) {
                    foreach ($hotel_rooms as $key => $array_value) {
                        if (count($array_value) > 0) {
                            foreach ($array_value as $key => $value) {
                                $value[2] = (isset($value[2])) ? $value[2] : 0;
                                $value[3] = (isset($value[3])) ? $value[3] : 0.000;
                                $value[1] = (isset($value[1])) ? $value[1] : 0.000;
                                $query_rooms = "INSERT INTO `hotel_room_relation` SET 
										`hotel_room_id`='{$key}',
										`hotel_booking_id`='{$query_hotels}',
										`room_count`='{$value[0]}',
										`room_price`='{$value[1]}',
										`extra_count`='{$value[2]}',
										`extra_price`='{$value[3]}',
                                        `room_check_in`='{$value[4]}',
                                        `room_check_out`='{$value[5]}',
                                        `room_number`='{$value[6]}';
                                        ";
                                if ($query_rooms) {
                                    $query_rooms = getwayConnect::getwaySend($query_rooms, true);
                                }
                            }

                        }
                    }
                }
            }
        }
        return $query_rooms;
    }

    public static function insertRoomExtra($query_hotels, $extra_data)
    {
        //query extra
        $query_extra = false;
        if ($extra_data) {
            $hotel_extra = base64_decode($extra_data);
            if ($hotel_extra) {
                $hotel_extra = json_decode($hotel_extra, true);
                if ($hotel_extra) {
                    foreach ($hotel_extra as $key => $array_value) {
                        if (count($array_value) > 0) {
                            foreach ($array_value as $key => $value) {
                                $value[1] = (isset($value[1]) && $value[1] > 0) ? $value[1] : 0.000;

                                $query_extra = "INSERT INTO `hotel_extra_relation` SET 
												`order_extra_id`='{$key}',
									 			`hotel_booking_id`='{$query_hotels}',
									  			`order_extra_count`='{$value[0]}',
									   			`order_extra_price`='{$value[1]}';";
                                if ($query_extra) {
                                    $query_extra = getwayConnect::getwaySend($query_extra, true);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $query_extra;
    }

    public static function insertHotelList($data_hotels, $travel_id, $data_travel = null)
    {
        print "<pre>";
        //add hotels connect to data travel
        if ($j_data = json_decode(base64_decode($data_hotels))) {
            $message = '';

            $ex_converted_array = json_decode(json_encode($j_data), true);
            $boolean_do_order_update = false;
            $query_update_custom = NULL;


            foreach ($ex_converted_array as $id_data_hotel_booking => $value) {


                $array_hotel_details = $ex_converted_array[$id_data_hotel_booking];


                //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                $custom_set_ids = "";
                $custom_set_names = "";


                foreach ($array_hotel_details as $custom_hotel_values) {

                    $hotel_array_ctm = $custom_hotel_values["hotel"];
//dd($hotel_array_ctm["checkin"]);
                    $custom_set_ids = $custom_set_ids . "v" . $hotel_array_ctm["id"] . "e ";


                    if (isset($hotel_array_ctm["name"])) {
                        $custom_set_names .= "<b>" . $hotel_array_ctm["name"] . "</b><br> ";
                    }


                    if (isset($hotel_array_ctm["checkin"]) && strlen($hotel_array_ctm["checkin"]) > 3) {
//                        $custom_set_names .= "CIN - " . substr($hotel_array_ctm["checkin"], 0, -3) . "<br> ";
                        $custom_set_names .= "CIN - " . $hotel_array_ctm["checkin"] . "<br> ";
                    }

                    if (isset($hotel_array_ctm["checkout"]) && strlen($hotel_array_ctm["checkout"]) > 3) {
//                        $custom_set_names .= "COUT - " . substr($hotel_array_ctm["checkout"], 0, -3) . "<br> ";
                        $custom_set_names .= "COUT - " . $hotel_array_ctm["checkout"] . "<br> ";
                    }

                    $custom_set_names .= "<hr>";


                    $boolean_do_order_update = true;
                }

//      ToDo:: update data_travel set update time


                if ($data_travel !== null) {
                    if ($data_travel['travel_hotel_name'] != $custom_set_names) {
                        $message .= ' Hotel: ' . $data_travel['travel_hotel_name'] . ' to ' . $custom_set_names;
                    }
                }

                $query_update_custom .= 'UPDATE `data_travel` SET travel_hotel_ids =\'' . $custom_set_ids . '\',  travel_hotel_name =\'' . $custom_set_names . '\' WHERE  id = ' . $id_data_hotel_booking;

            }


            if ($boolean_do_order_update) {
                getwayConnect::$db->query($query_update_custom);
            }


            //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            foreach ($j_data as $jk => $jv) {
                if ($jk == 0) {
                    foreach ($jv as $value) {
                        $rooms = base64_encode($value->rooms);
                        $extra = base64_encode($value->extra);
                        $hotel = $value->hotel;
                        $hotel_query = getwayConnect::getwaySend("
                                                                      INSERT INTO `data_hotel_booking` SET 
                                                                      `hotel_id` = '{$hotel->id}',
                                                                      `check_in` = '{$hotel->checkin}',
                                                                      `check_out` = '{$hotel->checkout}'
                                                                ", true);
                        if ($hotel_query) {
                            self::BookingTravel($hotel_query, $travel_id);
                            self::insertHotelRooms($hotel_query, $rooms);
                            self::insertRoomExtra($hotel_query, $extra);
                        }
                    }
                } else {
                    $hotel_booking_ids = array();
                    foreach ($jv as $value) {

                        $rooms = base64_encode($value->rooms);
                        $extra = base64_encode($value->extra);
                        $hotel = $value->hotel;
                        $hotel_query = false;
                        if ($hotel->booking_id <= 0) {
                            $hotel_query = getwayConnect::getwaySend("
                                                                      INSERT INTO `data_hotel_booking` SET 
                                                                      `hotel_id` = '{$hotel->id}',
                                                                      `check_in` = '{$hotel->checkin}',
                                                                      `check_out` = '{$hotel->checkout}'
                                                                ", true);
                            $hotel->booking_id = $hotel_query;
//                                var_dump($hotel_query);
                        } else {
                            $hotel_query = getwayConnect::getwaySend("
                                                                          UPDATE `data_hotel_booking` SET 
                                                                          `hotel_id` = '{$hotel->id}',
                                                                          `check_in` = '{$hotel->checkin}',
                                                                          `check_out` = '{$hotel->checkout}'
                                                                          WHERE `id` = '{$hotel->booking_id}'
                                                                    ");
                        }

                        $hotel_booking_ids[] = $hotel->booking_id;
                        if ($hotel_query) {
                            $hotel_query = $hotel->booking_id;
                            self::BookingTravel($hotel->booking_id, $travel_id);
                            self::updateHotelRooms($hotel_query, $rooms);
                            self::updateRoomExtra($hotel_query, $extra);
                        }
                    }
                    $to_str = implode(",", $hotel_booking_ids);
                    if (count($hotel_booking_ids) > 0) {
                        getwayConnect::getwaySend("DELETE FROM `travel_hotel_relation` WHERE `travel_id` = '{$travel_id}' AND `hotel_booking_id` NOT IN({$to_str})");
                    } else {
                        getwayConnect::getwaySend("DELETE FROM `travel_hotel_relation` WHERE `travel_id` = '{$travel_id}'");
                    }

                }

            }
            return $message;
        }
    }

    public static function updateHotelRooms($query_hotels, $data_hotels)
    {
        $remove_action = getwayConnect::getwaySend("DELETE FROM `hotel_room_relation` WHERE `hotel_booking_id`='{$query_hotels}';");
        if ($remove_action) {
            self::insertHotelRooms($query_hotels, $data_hotels);
        }

    }

    public static function BookingTravel($booking_id, $travel_id)
    {
        if ($data = getwayConnect::getwayData("SELECT * FROM `travel_hotel_relation` WHERE `travel_id` = '{$travel_id}' AND `hotel_booking_id` = '{$booking_id}'")) {
            return $data[0]['id'];
        } else {
            $item = 0;
            if ($booking_id > 0) {
                $item = getwayConnect::getwaySend("
                                          INSERT INTO `travel_hotel_relation` SET `travel_id` = '{$travel_id}', `hotel_booking_id` = '{$booking_id}'
                                    ", true);
            }

            return $item;
        }

    }

    public static function updateRoomExtra($query_hotels, $extra_data)
    {
        $remove_action = getwayConnect::getwaySend("DELETE FROM `hotel_extra_relation` WHERE `hotel_booking_id`='{$query_hotels}';");
        if ($remove_action) {
            self::insertRoomExtra($query_hotels, $extra_data);
        }
    }

    public static function getHotelRooms($query_hotels)
    {
        return getwayConnect::getwayData("SELECT * FROM `hotel_room_relation` WHERE `hotel_booking_id`='{$query_hotels}';", PDO::FETCH_ASSOC);
    }
    public static function getTravel($id)
    {
        return getwayConnect::getwayData("SELECT * FROM `data_travel` WHERE `id`='{$id}';", PDO::FETCH_ASSOC);
    }

    public static function getRoomExtra($query_hotels)
    {
        return getwayConnect::getwayData("SELECT * FROM `hotel_extra_relation` WHERE `hotel_booking_id`='{$query_hotels}';", PDO::FETCH_ASSOC);
    }

    public static function getPartnerData($pid)
    {
        return getwayConnect::getwayData("SELECT * FROM `travel_partner` WHERE `id` = {$pid};", PDO::FETCH_ASSOC);
    }
}

?>