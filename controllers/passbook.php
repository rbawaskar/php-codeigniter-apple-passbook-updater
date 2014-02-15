<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');


class Passbook extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('passbook', 'passbook');
	}

	public function index($version, $devices, $deviceId , $registrations, $passTypeId, $serialNo ){
		$method = strtolower($this->input->server('REQUEST_METHOD'));

		if ($method == "post") {
			if ($devices == "devices") {
				$this->register_pass($version, $devices, $deviceId , $registrations, $passTypeId, $serialNo);
			}else if ($devices == "log") {
				$this->log($version, $devices);
			}
		}else if ($method == "get") {
			if ($devices == "devices") {
				// version/devices/deviceLibraryIdentifier/registrations/passTypeIdentifier?passesUpdatedSince=tag
				$this->return_serials($version, $devices, $deviceId , $registrations, $passTypeId);
			}else if ($devices == "passes"){
				// version/passes/passTypeIdentifier/serialNumber
				$passTypeId = $deviceId;
				$serialNo = $registrations;
				$this->return_pass($version, "passes", $passTypeId, $serialNo);
			}
		}else if ($method == "delete") {
			$this->delete_pass($version, $devices, $deviceId , $registrations, $passTypeId, $serialNo);
		}
	}

	public function register_pass($version, $devices, $deviceId , $registrations, $passTypeId, $serialNo) {
		$authenticaton_token = end(explode(' ', $this->input->get_request_header('Authorization')));
		if (!$this->passes->is_valid_token($authenticaton_token, $serialNo)) {
			$this->output->set_status_header(401);
			exit;
		}

		if ($this->passes->is_device_already_registered($deviceId, $serialNo)){
			$this->output->set_status_header(200);exit;
		}else {
			$payload = json_decode(file_get_contents("php://input"), true);
			// error_log($_POST);
			$push_token = $payload['pushToken'];

			$insert_data['device_id'] = $deviceId;
			$insert_data['serial_number'] = $serialNo;
			$insert_data['pass_type_id'] = $passTypeId;
			$insert_data['push_token'] = $push_token;
			$this->passes->register_pass($insert_data);
			$this->output->set_status_header(201);exit;
		}
	}

	//send updated serials to apple
	public function return_serials($version, $devices, $deviceId , $registrations, $passTypeId) {
		if (!$this->passes->is_valid_device($deviceId)) {
			$this->output->set_status_header(404);exit;
		}
		$passesUpdatedSince = isset($_GET['passesUpdatedSince']) ? $_GET['passesUpdatedSince'] : "";
		$passes = $this->passes->get_device_passes($deviceId, $passTypeId, $passesUpdatedSince);
		if (count($passes) > 0) {
			$response_array = array(
				'lastUpdated' => date(DATE_ATOM),
				'serialNumbers' => $passes
				);
			header('Last-modified:' .date('r'));
			echo json_encode($response_array);
		}else{
			$this->output->set_status_header(204);exit;
		}
	}

	// return apple pass
	public function return_pass($version, $passes, $passTypeId, $serialNo) {
		$authenticaton_token = end(explode(' ', $this->input->get_request_header('Authorization')));
		if (!$this->passes->is_valid_token($authenticaton_token, $serialNo)) {
			$this->output->set_status_header(401);
			exit;
		}
		list($ft_order_id, $ticket_num) = explode('-', $serialNo);
		header('Last-modified:' .date('r'));
		$this->get_pass($ft_order_id, $ticket_num);
	}

	// delete apple pass
	public function delete_pass($version, $devices, $deviceId , $registrations, $passTypeId, $serialNo) {
		$authenticaton_token = end(explode(' ', $this->input->get_request_header('Authorization')));
		if (!$this->passes->is_valid_token($authenticaton_token, $serialNo)) {
			$this->output->set_status_header(401);
			exit;
		}
		$this->passes->delete_pass($deviceId, $serialNo);
	}

	public function log() {
		error_log("Printing error log pass");
		error_log(file_get_contents("php://input"));
	}

}

