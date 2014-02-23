
<?php
/**
 * Controller for librarian-specific modules
 *
 * @package 	icsls
 * @category 	Controller
 * @author 		Mark Carlo Dela Torre, Angela Roscel Almoro, Jason Faustino, Jay-ar Hernaez
 * @version 	1.0
*/
class Librarian extends CI_Controller{
	/**
	 * Constructor for the controller Librarian
	 *
	 * @access public
	*/
	public function Librarian(){
		parent::__construct();

		//Redirect if user is not logged in or not a librarian
		if(! $this->session->userdata('loggedIn') || $this->session->userdata('userType') != 'L'){
			redirect('login');
		}

		$this->load->helper(array('html', 'form'));
		$this->load->model("librarian_model");
		$this->load->library('table', 'input');
	}//end of constructor Librarian

	/**
	 * Default Librarian function - Loads view containing links to Librarian sub-modules
	 *
	 * @access public
	*/
	public function index(){
		$data["title"] = "Librarian Search Reference - ICS Library System";
		
		$this->load->view('librarian_main_view', $data);
	}//end of function index

	/* **************************************** SEARCH REFERENCE MODULE **************************************** */
	/**
	 * Loads the search reference view containing a form and input fields to search references stored in the database
	 *
	 * @access public
	*/
	public function search_reference_index(){
		$data['title'] = "Librarian Search Reference - ICS Library System";
		$this->load->view('search_reference_view', $data);
	}//end of function search_reference_index

	/* Displays search result based on the search_result function */
	/**
	 * Displays search result based on the user's input
	 *
	 * @access public
	*/
	public function display_search_results($query_id = 0, $offset = 0){
		$data['title'] = 'Librarian Search Reference - ICS Library System';

		$query_array = array(
			'category' => $this->input->get('selectCategory'),
			'text' => htmlspecialchars($this->input->get('inputText')),
			'sortCategory' => $this->input->get('selectSortCategory'),
			'row' => $this->input->get('selectRows'),
			'accessType' => $this->input->get('selectAccessType'),
			'orderBy' => $this->input->get('selectOrderBy'),
			'deletion' => $this->input->get('checkDeletion'),
			'match' => $this->input->get('radioMatch')
		);

		//Do not continue if user tried to make the database retrieval fail by editing URL's GET 
		foreach($query_array as $element):
			if($element === FALSE)
				redirect('librarian/search_reference_index');
		endforeach;

		$offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

		$data['total_rows'] = $this->librarian_model->get_number_of_rows($query_array);

		$results = $this->librarian_model->get_search_reference($query_array, $offset);

		$data['references'] = $results->result();
		$data['numResults'] = $results->num_rows();

		/* Initialize the pagination class */
		
		$this->load->library('pagination');
		$config['base_url'] = base_url() . "index.php/librarian/display_search_results?selectCategory={$_GET['selectCategory']}&inputText={$_GET['inputText']}&radioMatch={$_GET['radioMatch']}&selectSortCategory={$_GET['selectSortCategory']}&selectOrderBy={$_GET['selectOrderBy']}&selectAccessType={$_GET['selectAccessType']}&checkDeletion={$_GET['checkDeletion']}&selectRows={$_GET['selectRows']}";// . $query_id;
		$config['total_rows'] = $data['total_rows'];
		$config['per_page'] = $query_array['row']; 
		$config['page_query_string'] = TRUE;
		$config['full_tag_open'] = '<div class="pagination_table"><ul class="pagination">';
		$config['full_tag_close'] = '</ul></div>';
		$config['prev_link'] = '&lt; Prev';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['next_link'] = 'Next &gt;';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="active"><a href="#">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['first_link'] = FALSE;
		$config['last_link'] = FALSE;
		$this->pagination->initialize($config);

		//Load the Search View Page
		$this->load->view('search_reference_view', $data);
		
	}//end of function display_search_results

	/* **************************************** END OF SEARCH REFERENCE MODULE **************************************** */

	/* **************************************** VIEW REFERENCE MODULE **************************************** */
	/**
	 * View a reference specified by its row ID (which is set in the database)
	 *
	 * @access public
	*/
	public function view_reference(){
		$id = $this->uri->segment(3);
		if($id === FALSE)
			redirect('librarian');
	      
	    $result = $this->librarian_model->get_reference($id);
	    $data['reference_material'] = $result->result();
	    $data['number_of_reference'] = $result->num_rows();
	    $this->load->view('view_reference_view', $data);
	}//end of function view_reference

	/* **************************************** END OF REFERENCE MODULE **************************************** */

	/* **************************************** EDIT REFERENCE MODULE **************************************** */

	/**
	 * Loads initial state of the reference to be edited
	 *
	 * @access public
	*/
	public function edit_reference_index(){
		$data['title'] = 'Librarian Edit Reference - ICS Library System';

		if($this->uri->segment(3) === FALSE)
			redirect('librarian');

		$queryObj = $this->librarian_model->get_reference($this->uri->segment(3));

		$data['reference_material'] = $queryObj->result();
		$data['number_of_reference'] = $queryObj->num_rows();

		$this->load->view('edit_reference_view', $data);
	}//end of function edit_reference_index

	/**
	 * Edit reference based on the input of the user
	 *
	 * @access public
	*/
	public function edit_reference(){
		$this->load->helper('text');

		$id = $this->uri->segment(3);
		if($id === 	FALSE)
			redirect('librarian');

		//Filter the user's input of HTML special symbols
		$title = htmlspecialchars(mysql_real_escape_string(trim($this->input->post('title'))));
		$author = htmlspecialchars(mysql_real_escape_string(trim($this->input->post('author'))));
		$isbn = htmlspecialchars(mysql_real_escape_string(trim($this->input->post('isbn'))));
		$category = htmlspecialchars(mysql_real_escape_string(trim($this->input->post('category'))));
		$publisher = htmlspecialchars(mysql_real_escape_string(trim($this->input->post('publisher'))));
		$publication_year = htmlspecialchars(mysql_real_escape_string(trim($this->input->post('publication_year'))));
		$access_type = htmlspecialchars(mysql_real_escape_string(trim($this->input->post('access_type'))));
		$course_code = htmlspecialchars(mysql_real_escape_string($this->input->post('course_code')));
		$description = htmlspecialchars(mysql_real_escape_string(trim($this->input->post('description'))));
		$total_stock = htmlspecialchars(mysql_real_escape_string($this->input->post('total_stock')));

		//DO NOT TRUST the user's input. Server-side input validation
		/*if($total_stock <= 0)
			redirect('librarian/edit_reference_index/' . $id);			
		if(! in_array(strtoupper($category), array('B', 'S', 'C', 'J', 'M', 'T')))
			redirect('librarian/edit_reference_index/' . $id);
		if(! (intval($publication_year) >= 1000 AND intval($publication_year) <= date('Y')))
			redirect('librarian/edit_reference_index/' . $id);
		if(preg_match("/\A[A-Z]{2,3}\d{2,3}\z/", $course_code) === FALSE)
			redirect('librarian/edit_reference_index/' . $id);
		*/
		//Store the input from user to be passed on the model
	    $query_array = array(
	       	'id' => $id,
	       	'title' => $title,
	       	'author' => $author,
	       	'isbn' => $isbn,
	       	'category' => $category,
	       	'publisher' => $publisher,
	       	'publication_year' => $publication_year,
	       	'access_type' => $access_type,
	       	'course_code' => $course_code,
	       	'description' => $description,
	       	'total_stock' => $total_stock
	    );

	    $result = $this->librarian_model->edit_reference($query_array);
	    redirect('librarian/view_reference/' . $id);
	}//end of function edit_reference

	/* **************************************** END OF EDIT REFERENCE MODULE **************************************** */

	/* **************************************** DELETE REFERENCE MODULE **************************************** */
	/**
	 * Delete selected references specified by its respective checkbox
	 *
	 * @access public
	*/
    public function delete_reference(){
        $data['title'] = 'Delete Reference';
		
		$cannotBeDeleted = array();
		if(! empty($_POST['ch'])){
			if(count($_POST['ch']) > 0):
				$toDelete = $_POST['ch'];
				
				$toBeRemovedNumber = count($toDelete);

				for($i = 0; $i < $toBeRemovedNumber; $i++){
					$result = $this->librarian_model->delete_references($toDelete[$i]);
					if($result != -1)
						$cannotBeDeleted[] = $result;
				}
				 
			endif;
		}
		
		if(count($cannotBeDeleted) > 0){
			$data['forDeletion'] = $this->librarian_model->get_selected_books($cannotBeDeleted);
			$this->load->view('for_deletion_view',$data);
		}
		redirect(base_url() . 'index.php/librarian/search_reference_index','refresh');
    }//end of function delete_reference
	
	/**
	 * Updates for_deletion attribute of references in case they cannot be deleted immediately
	 *
	 * @access public
	*/
	public function change_forDeletion(){
		 $data['title'] = 'Delete Reference';
		 
		 if(! empty($_POST['ch'])):
			$toUpdate = $_POST['ch'];
			for($i = 0; $i < count($toUpdate); $i++){
				$this->librarian_model->update_for_deletion($toUpdate[$i]);
			}
		 endif;
		$readyResult = $this->librarian_model->get_ready_for_deletion();
		$data['readyDeletion']	= $readyResult;
		$idready = array();
		foreach($readyResult->result() as $row):
			$idready[] = $row->id;
		endforeach;
		
		$data['query'] = $this->librarian_model->get_other_books($idready);	
		redirect( base_url() . 'index.php/librarian','refresh');
	}//end of function change_forDeletion

	/* **************************************** END OF DELETE REFERENCE MODULE **************************************** */

	/* **************************************** ADD REFERENCE MODULE **************************************** */

	/**
	 * Loads the view for adding references
	 *
	 * @access public
	*/
	public function add_reference_index(){
		$data['title'] = 'Librarian Add Reference - ICS Library System';
		$this->load->view('add_view', $data);
	}//end of function add_reference_index

	/**
	 * Add a reference to the database
	 *
	 * @access public
	*/
	public function add_reference(){
		$data['title'] = 'Librarian Add Reference - ICS Library System';
		$data['message']= '';

		if($this->input->post('submit')) {
			$data = array(
	        	'TITLE' => htmlspecialchars(trim($this->input->post('title'))),
	            'AUTHOR' => htmlspecialchars(trim($this->input->post('author'))),
	            'ISBN' => htmlspecialchars($this->input->post('isbn')),
	            'CATEGORY' => htmlspecialchars($this->input->post('category')),
	            'DESCRIPTION' => htmlspecialchars(trim($this->input->post('description'))),
	            'PUBLISHER' => htmlspecialchars(trim($this->input->post('publisher'))),
	            'PUBLICATION_YEAR' => htmlspecialchars($this->input->post('year')),
	            'ACCESS_TYPE' => htmlspecialchars($this->input->post('access_type')),
	            'COURSE_CODE' => htmlspecialchars($this->input->post('course_code')),
	            'TOTAL_STOCK' => htmlspecialchars($this->input->post('total_stock')),
	            'TIMES_BORROWED' => '0',
	            'FOR_DELETION' => 'F'    
        	);
			$data['TOTAL_AVAILABLE'] = $data['TOTAL_STOCK'];
			$data['TOTAL_IN_STOCK'] = $data['TOTAL_STOCK'];

			//Setting empty fields that can be NULL to NULL
			if($data['ISBN'] == '')
				$data['ISBN'] = NULL;
			if($data['DESCRIPTION'] == '')
				$data['DESCRIPTION'] = NULL;
			if($data['PUBLISHER'] == '')
				$data['PUBLISHER'] = NULL;
			if($data['PUBLICATION_YEAR'] == '')
				$data['PUBLICATION_YEAR'] = NULL;

			//Server-side Input validation
			//Missing not-NULLable data validation
			if($data['TITLE'] == '' OR $data['AUTHOR'] == '' OR $data['CATEGORY'] == '' OR $data['ACCESS_TYPE'] == '' OR $data['COURSE_CODE'] == '' OR $data['TOTAL_AVAILABLE'] == '')
				redirect('librarian/add_reference');
			//Category fixed pre-defined set of values validation
			if(! in_array($data['CATEGORY'], array('B', 'M', 'S', 'J', 'T', 'C')))
				redirect('librarian/add_reference');
			//Access Type fixed pre-defined set of values validation
			if(! in_array($data['ACCESS_TYPE'], array('S', 'F')))
				redirect('librarian/add_reference');
			//Publication Year value validation
			if($data['PUBLICATION_YEAR'] != '' && (intval($data['PUBLICATION_YEAR']) < 1900 OR intval($data['PUBLICATION_YEAR']) > intval(date('Y'))))
				redirect('librarian/add_reference');
			//Total Stock value validation
			if(intval($data['TOTAL_STOCK']) < 1)
				redirect('librarian/add_reference');
			if(preg_match("/\A[A-Z]{2,3}[0-9]{1,3}\z/", $data['COURSE_CODE']) == 0)
				redirect('librarian/add_reference');

		    $this->librarian_model->add_data($data);
		    $data['message']= 'You have successfully added a reference material';
		   $this->load->view("addReference_view", $data);
		}else{
			$this->load->view("addReference_view", $data);
		}
	}//end of function add_reference

	/**
	 * Loads and validates the file uploaded by the user
	 *
	 * @access public
	*/
	public function file_upload(){
		$data['title'] = 'Librarian Add Reference - ICS Library System';

		if($this->input->post()){
			$config_arr = array(
	            'upload_path' => './uploads/',
	            'allowed_types' => 'text/plain|text/csv|csv',
	            'max_size' => '2048'
	        );

	        $this->load->library('upload', $config_arr);

			if(! $this->upload->do_upload('csvfile')){
				$data['error'] = $this->upload->display_errors();
				$this->load->view("fileUpload_view", $data);
			}
			else{
				$uploadData = array('upload_data' => $this->upload->data());
				$filename='./uploads/'.$uploadData['upload_data']['file_name'];
				$this->load->library('csvreader');
		        $data['csvData'] = $this->csvreader->parse_file($filename);
				$this->load->view("uploadSuccess_view", $data);
			}
		}
		else{
			$this->load->view("fileUpload_view", $data);     
		}
	}//end of function file_upload

	/**
	 * Adds multiple references to the database using the data in the file
	 *
	 * @access public
	*/
	public function add_multipleReferences(){
		$data['title'] = 'Librarian Add Reference - ICS Library System';
		//$this->load->view("fileUpload_view", $data);
		if($this->input->post()){
		    $count = $this->input->post('rowCount');

		    for($i = 0; $i < $count; $i++) {
				$data[$i] = array(
					'TITLE' => htmlspecialchars(mysql_real_escape_string($this->input->post('title' . $i))),
					'AUTHOR' => htmlspecialchars(mysql_real_escape_string($this->input->post('author' . $i))),
					'ISBN' => htmlspecialchars(mysql_real_escape_string($this->input->post('isbn' . $i))),
					'CATEGORY' => htmlspecialchars(mysql_real_escape_string($this->input->post('category' . $i))),
					'DESCRIPTION' => htmlspecialchars(mysql_real_escape_string($this->input->post('description' . $i))),
					'PUBLISHER' => htmlspecialchars(mysql_real_escape_string($this->input->post('publisher' . $i))),
					'PUBLICATION_YEAR' => htmlspecialchars(mysql_real_escape_string($this->input->post('year' . $i))),
					'ACCESS_TYPE' => htmlspecialchars(mysql_real_escape_string($this->input->post('access_type' . $i))),
					'COURSE_CODE' => htmlspecialchars(mysql_real_escape_string($this->input->post('course_code' . $i))),
					'TOTAL_STOCK' => htmlspecialchars(mysql_real_escape_string($this->input->post('total_stock' . $i))),
					'TIMES_BORROWED' => '0',
					'FOR_DELETION' => 'F'    
				);
				$data[$i]['TOTAL_AVAILABLE'] = $data[$i]['TOTAL_STOCK'];
				$data[$i]['TOTAL_IN_STOCK'] = $data[$i]['TOTAL_STOCK'];
		    }

	    	$this->librarian_model->add_multipleData($data, $count);
		    redirect('librarian/file_upload','refresh');
		}
	}//end of function add_multipleReferences

	/* **************************************** END OF ADD REFERENCE MODULE **************************************** */

	/**
	 * Displays information about the libarian
	 *
	 * @access public
	*/
	public function view_profile(){
		$data['title'] = "View Profile - Onlib";
		$this->load->model('administrator_model');

		$data['results'] = $this->administrator_model->get_profile($this->session->userdata('id'));

		$this->load->view('user_profile_view', $data);
	}

	/* **************************************** GENERATE REPORT MODULE **************************************** */
	public function view_report_index(){
		$data["title"] = "Home - ICS Library System";
		$this->load->view("report_generation_view", $data);
	}

	public function view_report(){
		$data['title'] = "Report - ICS Library System";
		$this->load->library('fpdf/fpdf');//load fpdf class; a free php class for pdf generation

		$type = $_POST['print_by'];
		$result = $this->librarian_model->get_data($type);
		if($result != NULL){
			$data['result'] = $result->result();
			$data['mostBorrowed'] = $this->librarian_model->get_popular()->result();
			$this->load->view('pdf_report_view', $data);
		}
		else{
			redirect('home');
		}
	}

	/* **************************************** END OF GENERATE REPORT MODULE **************************************** */

	/**
	 * Decrements/Increments the total_available of a reference
	 *
	 * @access public
	*/
	public function claim_return(){
		$referenceId = $this->uri->segment(3);
		$flag = $this->uri->segment(4);

		if(intval($referenceId) > 0)
			$this->librarian_model->claim_return_reference($referenceId, $flag);

		redirect('librarian/view_reference/' . $referenceId);
	}

	/**
	 * Display all references borrowed, reserved and waitlisted, and users who borrowed, reserved, and waitlisted such references
	 *
	 * @access public
	*/
	//public function trans
	
}

?>