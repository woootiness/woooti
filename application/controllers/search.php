<?php

class Search extends CI_Controller{
	public function Search(){
		parent::__construct();
		//load libraries,models, helpers
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->model('user_model');
		$this->load->library('pagination');
	}

	public function index(){
		$data['title'] = "Home - ICS Library System";
		$data['header'] = "Search";
		$this->load->view("search_view", $data);
	} 
	/* Description: Calls the search_reference_material function to get search results
	   based on the User's search input */
	public function search_rm(){
		$data["title"] = "Search - ICS Library System";
		$keyword = $this->input->get('keyword');
		$keyword = ltrim($keyword);
		$keyword = rtrim($keyword);

		//replace special characters with nothing
		$order  = array('\\','\/','@','!','#','&','$','%','^','*','(',')','+','=',',','.','<','>','?','[',']',':','\'');
		$keyword = str_replace($order, '', $keyword);

		$sessionData=array('keyword'=>$keyword);
		$this->session->set_userdata($sessionData);
		$config['per_page'] = 10;
		$config['base_url'] = base_url("index.php/search/search_rm?keyword={$_GET['keyword']}");
		$config['num_links']= 10;
		$config['page_query_string'] = TRUE;
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Previous';
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
		if($keyword==null){
			redirect("home");
			//empty keyword
		}
		else{
			if(!isset($_GET['per_page']))//used for pagination
				$_GET['per_page'] = 0;
			$order2  = array('\\','\/','@','!','#','&','$','%','^','*','(',')','+','=',',','.','<','>','?','[',']',':','\'','a','b','c',
			'd','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G',
			'H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			
			$temporary = $_GET['per_page'];
			$temporary = str_replace($order2, '', $temporary);
			$result1 = $this->user_model->search_reference_material($keyword,$config['per_page'],$temporary);
			
			$data['flags'] = FALSE;
			if($result1 != NULL){
				$data['rows'] = $result1->result();
				$config['total_rows'] = $this->user_model->search_reference_material2($keyword)->num_rows();
				//^we get all rows
				
				$this->pagination->initialize($config);

				$data['totalrefmat'] = $config['total_rows'];//we use this to display some info

				$data['flags'] = FALSE;
				$this->load->view('search_result_view', $data);
			}
			//if there are no results, it means there are more keywords
			else{
				$data['rows'] = null;	// resets to null
				//get the rows with tokenizer
				$result = $this->user_model->search_reference_material_token($keyword,$config['per_page'],$_GET['per_page']);
				
				//if($result != NULL){

					$data['rows'] = $result->result();
					$temp = $this->user_model->search_reference_material_token2($keyword);
					$config['total_rows'] = $temp->num_rows();
					$this->pagination->initialize($config);

					$data['totalrefmat'] = $temp->num_rows();
					if($result != NULL)
						$data['flags'] = FALSE;
					else $data['flags'] = TRUE;//no materials found
					$this->load->view('search_result_view', $data);
				//}
				//else{
					//it means that no reference material is found
					//test case only
					//$this->load->view('no_materials_view',$data);
				//}
			}
			
		}
	}


	/* Description: function for reserving/waitlisting */
	public function transaction(){
		$data['title'] = "Home - ICS Library System";
		$data['header'] = "Search";
		//$data['rows'];
		$data['flags']=FALSE;
		$referenceId = $this->input->get('id');
		$userId = $this->session->userdata('id');
		//var_dump($referenceId);
		if($referenceId==NULL){
			$referenceId = $this->uri->segment(3);
		}
		$user_type = $this->session->userdata('user_type');
		$input = $this->session->userdata('keyword');
		$email = $this->session->userdata('email_address');	
		$firstName = $this->session->userdata('firstName');	
		//echo $email;
		//if "Reserve" button was clicked
		if(isset($_GET['reserve'])){
			$reserveStatus = $this->user_model->reserve_reference_material($referenceId, $userId, $user_type);

			//var_dump($reserveStatus);

			if($reserveStatus == FALSE){	//if conditions in reserving were not satisfied
				echo "Reserve Action Denied: <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;3 possible reasons <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. You are not allowed to access this book. <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. You have reached the limit of borrowing books. <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. You have reserved this book already. <br/><br/><br/>";
				$data['rows'] = $this->user_model->search_reference_materials($input);
				$data['flag'] = 1;
				if($data['rows'] != NULL)
					$data['flags']=FALSE;
				else $data['flags']=TRUE;
				$this->load->view('search_result_view.php', $data);

			}
			else if($reserveStatus == 1){	//successful reserve
				echo "Reference material was successfully reserved.";
				$sessionData = array('canWaitlist' => FALSE, 'referenceId' => $referenceId, 'canReserve' => FALSE);
				$this->session->set_userdata($sessionData);
				$data['rows'] = $this->user_model->search_reference_materials($input);
				$data['flag'] = 1;
				//call to mail
				
				if($data['rows'] != NULL)
					$data['flags']=FALSE;
				else $data['flags']=TRUE;
				
				$this->send_email($email,$firstName);

				$this->load->view('search_result_view.php', $data);
				
			}
			else{	//if the reference material is out of stock
				$sessionData = array('canWaitlist' => true, 'referenceId' => $referenceId, 'canReserve' => FALSE);
				$this->session->set_userdata($sessionData);
				$data['rows'] = $this->user_model->search_reference_materials($input);//material
				$data['flag'] = 0;
				if($data['rows'] != NULL)
					$data['flags']=FALSE;
				else $data['flags']=TRUE;
				$this->load->view('search_result_view.php', $data);
			}
		 //if "Waitlist" button was clicked
		}else if(isset($_GET['waitlist'])){
			$waitlistStatus = $this->user_model->waitlist_reference_material($referenceId, $userId, $user_type);
	
			if($waitlistStatus == FALSE){	//if conditions in waitlisting were not satisfied
				echo "Waitlist Denied: <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;3 possible reasons <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. You are not allowed to access this book. <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. You have reached the limit of maximum wait list. <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. You have waitlisted on this book already. <br/><br/><br/>";
				$data['rows'] = $this->user_model->search_reference_materials($input);//binalik muna, walang s yung isa
				$data['flag'] = 1;
				if($data['rows'] != NULL)
					$data['flags']=FALSE;
				else $data['flags']=TRUE;
				$this->load->view('search_result_view.php', $data);
			}
			else if($waitlistStatus == 1){	//successful wait list
				echo "Reference material was successfully waitlisted.";
				$sessionData = array('canWaitlist' => FALSE, 'referenceId' => $referenceId, 'canReserve' => FALSE);
				$this->session->set_userdata($sessionData);
				$data['rows'] = $this->user_model->search_reference_materials($input);
				$data['flag'] = 1;
				if($data['rows'] != NULL)
					$data['flags']=FALSE;
				else $data['flags']=TRUE;
				$this->load->view('search_result_view.php', $data);
			}
			else{	//if the reference material is still available
				$sessionData = array('canReserve' => true, 'referenceId' => $referenceId, 'canWaitlist' => FALSE);
				$this->session->set_userdata($sessionData);
				$data['rows'] = $this->user_model->search_reference_materials($input);
				$data['flag'] = 0;
			if($data['rows'] != NULL)
					$data['flags']=FALSE;
				else $data['flags']=TRUE;
				$this->load->view('search_result_view.php', $data);
			}
		 //if neither of the two buttons (Reserve, Waitlist) were clicked
		}else{
				//$data['bookInfo'] = $this->user_model->search_reference_material($input);
				$data['flag'] = 1;
				$data['rows'] = NULL;

				$this->load->view('search_result_view.php', $data);
		}
	}


	
	/**
	* Function calls the model function responsible for retrieving data; it processes those
	* checkboxes that has been ticked
	*	@access	public
	*/
	public function advanced_search_reference(){
		$data["title"] = "Advanced Search - ICS Library System";

		$tempArray = array();//for keywords
		$tempArrayValues = array();//for the values
		//replace special characters with nothing
		$order  = array('\\','\/','@','!','#','&','$','%','^','*','(',')','+','=',',','.','<','>','?','[',']',':','\'');


		$order2  = array('\\','\/','@','!','#','&','$','%','^','*','(',')','+','=',',','.','<','>','?','[',']',':','\'','a','b','c',
			'd','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G',
			'H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$query = "";//for query

		/*
		the following codes will check if the checkbox is marked
		*/
		if(in_array("title", $_GET['projection'])){
			$keywordTitle = $this->input->get('title');
			if($keywordTitle==null){	//didn't type anything
				redirect('home');
			}

			//trim whitespaces and special characters
			$keywordTitle = ltrim($keywordTitle);
			$keywordTitle = rtrim($keywordTitle);
			$keywordTitle = str_replace($order, '', $keywordTitle);
			//we will build up the search query using string concatenation
			$query .= "title like '%$keywordTitle%'";
			array_push($tempArray,'title');	//push it to the array
			array_push($tempArrayValues,$keywordTitle);
		}
		//if author is checked
		if(in_array("author", $_GET['projection'])){
			$keywordAuthor = $this->input->get('author');
			if($keywordAuthor==null){
				redirect('home');
			}


			//trim whitespaces and special characters
			$keywordAuthor = ltrim($keywordAuthor);
			$keywordAuthor = rtrim($keywordAuthor);
			$keywordAuthor = str_replace($order, '', $keywordAuthor);
			if ( in_array('title',$tempArray) ) {
				//^ we check if previous checkboxes were marked
				$query .= " or author like '%$keywordAuthor%'";
			}
			else{
				//no other checkboxes were marked
				$query .= " author like '%$keywordAuthor%'";
			}
			array_push($tempArray,'author');
			array_push($tempArrayValues,$keywordAuthor);
		}

		//if year_published is checked
		if(in_array("year_published", $_GET['projection'])){
			$keywordYearPublished = $this->input->get('year_published');
			if($keywordYearPublished ==null){
				redirect('home');
			}
			$keywordYearPublished = ltrim($keywordYearPublished);
			$keywordYearPublished = rtrim($keywordYearPublished);
			$keywordYearPublished = str_replace($order, '', $keywordYearPublished);

			if ( in_array('title',$tempArray) || in_array('author',$tempArray)) {
				//^ we check if previous checkboxes were marked
				$query .= " or publication_year like '%$keywordYearPublished%'";
			}
			else{
				//no other checkboxes were marked
				$query .= " publication_year like '%$keywordYearPublished%'";
			}
			array_push($tempArray,'year_published');
			array_push($tempArrayValues,$keywordYearPublished);
		}
		
		//if publisher is checked
		if(in_array("publisher", $_GET['projection'])){
			$keywordPublisher = $this->input->get('publisher');
			if($keywordPublisher==null){
				redirect('home');
			}
			
			$keywordPublisher = ltrim($keywordPublisher);
			$keywordPublisher = rtrim($keywordPublisher);
			$keywordPublisher = str_replace($order, '', $keywordPublisher);
			if ( in_array('title',$tempArray) || in_array('author',$tempArray) || in_array('year_published', $tempArray)) {
				//^ we check if previous checkboxes were marked
				$query .= " or publisher like '%$keywordPublisher%'";
			}
			else{
				//no other checkboxes were marked
				$query .= " publisher like '%$keywordPublisher%'";
			}
			array_push($tempArray,'publisher');
			array_push($tempArrayValues,$keywordPublisher);
		}

		//if course_code is checked
		if(in_array('course_code',$_GET['projection'])){
	    	$keywordCourseCode = $this->input->get('course_code');
	    	if($keywordCourseCode==null){
	    		redirect('home');
			}

			$keywordCourseCode = ltrim($keywordCourseCode);
			$keywordCourseCode = rtrim($keywordCourseCode);
			$keywordCourseCode = str_replace($order, '', $keywordCourseCode);
			if ( in_array('title',$tempArray) || in_array('author',$tempArray) || in_array('year_published', $tempArray) || in_array('publisher', $tempArray)) {
				//^ we check if previous checkboxes were marked
				$query .= " or course_code like '%$keywordCourseCode%'";
			}
			else{
				//no other checkboxes were marked
				$query .= " course_code like '%$keywordCourseCode%'";
			}
			array_push($tempArray,'course_code');
			array_push($tempArrayValues,$keywordCourseCode);
		}
		if($tempArray == null)//check if no checkbox is checked
			redirect('home');

		//the default sort is by title ascending
		$sort="order by title asc";

		//we check what radio button is marked then we put the appropriate query
		if(isset($_GET['sort'])){
			if($_GET['sort'] == 'sortalpha'){
				$sort = "order by title asc";
			}
			elseif ($_GET['sort'] == 'sortbeta') {
				$sort = "order by title desc";
			}
			elseif ($_GET['sort'] == 'sortyear') {
				$sort = "order by publication_year desc";
			}
			else{
				$sort = "order by author asc";
			}
		}


		//we need this for the pagination uri
		$q1 = $tempArray[array_search('title', $tempArray)];
		$q2 = $tempArray[array_search('author', $tempArray)];
		$q3 = $tempArray[array_search('year_published', $tempArray)];
		$q4 = $tempArray[array_search('publisher', $tempArray)];
		$q5 = $tempArray[array_search('course_code', $tempArray)];
		if(!isset($_GET['per_page']) || $_GET['per_page'] == null)
			$_GET['per_page'] = 0;

		$temporary = $_GET['per_page'];
		$temporary = str_replace($order2, '', $temporary);

		$data['temparray'] = $tempArray;
		$data['temparrayvalues'] = $tempArrayValues;
		$config['per_page'] = 10;
		$config['base_url'] = base_url("index.php/search/advanced_search_reference?projection%5B%5D={$q1}&title={$_GET['title']}&projection%5B%5D={$q2}&author={$_GET['author']}&projection%5B%5D={$q3}&year_published={$_GET['year_published']}&projection%5B%5D={$q4}&publisher={$_GET['publisher']}&projection%5D%5B={$q5}&course_code={$_GET['course_code']}");
		$config['num_links']= 10;
		$config['page_query_string'] = TRUE;
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Previous';
	//------------modify
		$reftype = $this->input->get('reftype');
		//var_dump($reftype);
//"select * from reference_material where category = (select * from reference_material where category = 'B')
		/*
		SELECT * 
FROM reference_material
WHERE category
IN (

SELECT category
FROM reference_material
WHERE category =  'B'
)*/
		//we run the query
		$data['flags']=NULL;
		$result = $this->user_model->advanced_search("Select * from reference_material where {$query} {$sort} limit {$temporary},{$config['per_page']}");
		$result2 = $this->user_model->advanced_search("Select * from reference_material where {$query} {$sort}");
		if($result != NULL)
			$data['flags']=FALSE;
		else $data['flags']=TRUE;
			$data['rows'] = $result->result();

			$config['total_rows'] = $result2->num_rows();//set the total number of rows
			$this->pagination->initialize($config);
			$data['totalrefmat'] = $result2->num_rows();
			$this->load->view('search_result_view', $data);
//		}
		//else{
			//it means that no reference material is found
			//$this->load->view('no_materials_view',$data);

			//we previously stored in the array the values of those that are checked;
			//$tempArrayValues[array_search('title')]
			/*
SELECT * FROM `reference_material` WHERE title like '%a%' or author like 
'%carlo%' IN (Select * from `reference_material` where category = 'B')
			*/

		//}

	}


	/**
	*	Function displays all information of the book
	*	@access	public
	*/
	public function view_reference(){
		$data['title'] = "Book - ICS Library System";

		$bookid = $this->uri->segment(3);
		$result = $this->user_model->view_reference_material($bookid);
		$data['rows'] = $result->result();
		$this->load->view('view_results_view', $data);
	}

	/**
	 * function to email after successful reserve
	 *
	 * @access	public
	 */
	function send_email($email_add,$name){

		$config = Array(
			'protocol' => "smtp",
			'smtp_host' => "ssl://smtp.googlemail.com",
			'smtp_port' => 465,
			'smtp_user' => "user.librarian@gmail.com",
			'smtp_pass' => "userlibrarian",//password
			'mailtype'  => 'html',
			'charset' => 'utf-8'
		);

		$date = date('Y-m-d H:m:s');
		$newdate = strtotime ( '+3 Days' , strtotime( $date ));
		$newdate = date('y-m-d',$newdate);
		$day= date("l", strtotime($date) );
		
		if($day == "Saturday")
			{
				$duedate = strtotime ( '+2 day' , strtotime( $newdate ));
				$duedate = date('F j, Y',$duedate);
			}
		elseif($day == "Sunday")
			{
				$duedate = strtotime ( '+1 day' , strtotime( $newdate ));
				$duedate = date('F j, Y',$duedate);
			}
		else
		{
			$duedate = date('F j, Y',strtotime($newdate));
		}
		$date = date('F j, Y',strtotime($date));

		$this->load->library("email", $config);//we pass our configuration
		$this->email->set_newline("\r\n");

		$this->email->from("user.librarian@gmail.com", "ICS Librarian");
		//sample only
		$this->email->to($email_add);
		$this->email->subject("Reference Material Reservation");
		$this->email->message("Dear {$name}, <br/>last ".$date." you have reserved a book using ICS OnLib."."<br/>This will be due on : <h1>".$duedate."</h1>You must claim the book on or before the due date. 
			Thank you for your cooperation.<br/>Sincerely,<br/><h3>ICS Librarian</h3>");

		if($this->email->send()){//if sent successfully
			echo " Reservation details was sent to your email.";
		}
		else{
			redirect('home');
		}
	}
}?>