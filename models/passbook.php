<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Passbook extends CI_Model {

	public function insert_pass($data) {
		$query = $this->db->get_where('apple_passes', array('serial_number' => $data['serial_number']));
		if ($query->num_rows() == 0){
			$insert_values = $this->db->insert_string('apple_passes',$data);
			$this->db->query($insert_values);
		}
	}

	public function register_pass($data) {
		$insert_values = $this->db->insert_string('apple_registrations',$data);
		$this->db->query($insert_values);
	}

	public function is_device_already_registered($device_id, $serial_no) {
		$query = $this->db->get_where('apple_registrations', array('serial_number' => $serial_no, 'device_id' => $device_id));
		return ($query->num_rows() >= 1) ? true:false;
	}

	public function is_valid_token($authentication_token, $serial_no ) {
		$query = $this->db->get_where('apple_passes', array('serial_number' => $serial_no, 'authentication_token' => $authentication_token));
		return ($query->num_rows() >= 1) ? true:false;
	}

	public function is_valid_device($deviceId) {
		$query = $this->db->get_where('apple_registrations', array('device_id' => $deviceId));
		return ($query->num_rows() >= 1) ? true:false;
	}

	public function get_device_passes($device_id, $pass_type_id, $updated_since) {
		$serials = array();
		$sql_updated = "SELECT DISTINCT(p.serial_number) as serial_number FROM apple_registrations r LEFT JOIN apple_passes p
						ON r.serial_number = p.serial_number AND p.pass_type_id = r.pass_type_id WHERE p.pass_type_id = '".$pass_type_id."' AND device_id = '".$device_id."'";
		if ($updated_since!="") {
			$sql_updated .= " AND last_update_datetime > '".$updated_since."'";
		}
		$result = $this->db->query($sql_updated)->result_array();
		if (is_array($result)) {
			foreach ($result as $value) {
				$serials[] = $value['serial_number'];
			}
		}
		return $serials;
	}

	public function delete_pass($device_id, $serial_no) {
		$query = $this->db->delete('apple_registrations', array('device_id' => $device_id, 'serial_number' => $serial_no));
	}

	public function check_for_updates() {
		//check for passbook updates
		// if true then update last_update_datetime in apple_passes
		// $update_data = array('last_update_datetime' => time());
		// $update_seats = $this->db->update_string('apple_passes', $update_data, $update_where);
		// $this->db->query($update_string);

		// fetch the tokens for sending push
		// $tokens_sql = "SELECT authentication_token FROM apple_registrations r 
		// 			LEFT JOIN apple_passes p ON r.serial_number = p.serial_number WHERE pass_id in (?)";
		// $get_tokens = $this->db->query($tokens_sql, $updated_pass_ids)->result_array();

		// send push notifications using your fav libarary
		// $this->load->library('apple_push/Apns');
		// $apns = new Apns();
		// $apns->send_push($get_tokens);
	}

}