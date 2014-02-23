<head>

</head>
<?php $this->load->view('includes/header'); ?>
<script type="text/javascript">	
			window.onload = function(){
					createForm.password.onblur = validatePassword;
					createForm.repassword.onblur = validateRePass;
			}
			
			function validatePassword(){
				msg = "Invalid input!  ";
				str = createForm.password.value;
				
				if(str == "") msg+="Password is required. ";
				if(str.match(/^[a-z]$/)) msg="Strength: Weak";
				else if(str.match(/^[0-9]+$/)) msg="Strength: Weak";
				else if(str.match(/^[a-z0-9]+$/)) msg="Strength: Medium";
				else if(str.match(/^[a-zA-Z0-9]+$/)) msg="Strength: Strong";
				
				if(msg == "Invalid input!  ") msg="";
				document.getElementsByName("helppass")[0].innerHTML=msg;
				
			}
				
			function validateRePass(){
				msg = "Invalid input!  ";
				str = createForm.repassword.value;
				
				if(str != createForm.password.value) msg+="Password does not match. ";
				else msg="Passwords match.";
				
				if(msg == "Invalid input!  ") msg="";
				document.getElementsByName("helprepass")[0].innerHTML=msg;
				if(msg == "") return true;
				
			}	
		</script>
<div id="content">
	<div id="left">
	<div class = "profile-description">

		<form action="<?=base_url()."index.php/borrower/save"?>" method="post" name="createForm"><!--Profile Form-->
			<?=$save_message?> <!--Save Message-->
			<?=$username_exist?><!--Username Message-->
		<br/>

			<div id ="profile_picture_div">
				<img src="<?= base_url('img/download.jpg');?>" width="200" height="200">
				<form action="<?=base_url()."index.php/borrower/change_profile_picture"?>" method="post" accept-charset="utf-8" enctype="multipart/form-data"><!--Profile Form-->
				Profile picture: <input type='file' id='profile_picture' name='profile_picture' required/><br/>
				<?=form_submit('submit','Change Profile Picture')?>
				</form>
			</div>

		
		
			<div id="profile_div">

			<form action="<?=base_url()."index.php/profile/save"?>" method="post" name="createForm"><!--Profile Form-->
			<?=$save_message?> <!--Save Message-->
			<?=$username_exist?><!--Username Message-->
		        <table id="prof_form">
		            <tr>
						<h2 id="prof_name"><?=$query_user->last_name?>, <?=$query_user->first_name?> <?=$query_user->middle_name?></h2>
		            </tr>
		     
		     	    <tr>
		            	<?php if($query_user->user_type=='S') { //if user is student?>
		            		<td align="right"><button type="button" class="btn btn-primary" >Student Number</button></td>
		            		<td align="right"> <input type="text" class="form-control" DISABLED id="student_number" value="<?=$query_user->student_number?>" name="student_number" pattern="^[0-9][0-9\-]{11}[0-9]$"  width="5em"></td>
						<?php }?>
						<?php if($query_user->user_type=='F') { //if user is FACULTY?>
		            		<td align="right"><button type="button" class="btn btn-primary" >Employee Number</button></td>
		            		<td align="right"> <input type="text" class="form-control" DISABLED id="student_number" value="<?=$query_user->employee_number?>" name="student_number" pattern="^[0-9][0-9\-]{11}[0-9]$"  width="5em"></td>
						<?php }?>
		            </tr>
		            <tr>
		            	<?php if($query_user->user_type=='S') { //if user is student?>
		            		<td align="right"><button type="button" class="btn btn-primary" >College</button></td>
		            		<td align="right"> <input type="text" class="form-control" DISABLED id="college" value="<?=$query_user->college?>" name="college" pattern="^[0-9][0-9\-]{11}[0-9]$"  width="5em"></td>
						<?php }?>
		            </tr>
		            <tr>
		            	<?php if($query_user->user_type=='S') { //if user is student?>
		            		<td align="right"><button type="button" class="btn btn-primary">Degree Course</button></td>
		            		<td align="right"> <input type="text" class="form-control" DISABLED id="degree" value="<?=$query_user->degree?>" name="degree" pattern="^[0-9][0-9\-]{11}[0-9]$"  width="5em"></td>
						<?php }?>
		            </tr>
		            <tr>
		                <td align="right"><button type="button" class="btn btn-primary">Email Address</button></td>
		                <td align="right"> <input type="text" class="form-control" DISABLED id="email_address" value="<?=$query_user->email_address?>" name="email_address" pattern = "^([a-z0-9._]{2,}@[a-zA-Z0-9.-]{2,}\.[a-zA-Z]{2,})(\.[a-zA-Z])*$"  width="5em"></td>
		            </tr>
		            <tr>
		                <td align="right"><button type="button" class="btn btn-primary"  >Username</button></td>
		                <td align="right"> <input type="text" class="form-control" id="username" name="username" value="<?=$query_user->username?>" pattern = "[A-Za-z_.0-9]+" width="3em" ></td>
		            </tr>
		            <tr>
		                <td align="right"><button type="button" class="btn btn-primary" width="3em" >Password</button></td>
		                <td align="right"> <input type="password" class="form-control" id="password" name="password" pattern="^[a-zA-Z0-9]{0,32}$" width="3em"
		                	></span></td>
		                <td align="right"><span name="helppass"></td>
		            </tr>
		            <tr>
		                <td align="right"><button type="button" class="btn btn-primary" width="3em" >Confirm Password</button></td>
		                <td align="right"> <input type="password" class="form-control" id="repassword" name="repassword" pattern="^[a-zA-Z0-9]{0,32}$" width="3em" ></span></td>
		                <td align="right"><span name="helprepass"></td>
		            </tr>
		            <tr>
			            <td align="right"><button type="button" class="btn btn-primary" width="3em" >College Address</button></td>
		                <td align="right"> <textarea class="form-control" id="college_address" name="college_address" pattern="^[A-Z0-9]{0,300}$"><?=$query_user->college_address?></textarea></td>
		            </tr>
		            <tr>
		                <td align="right"><button type="button" class="btn btn-primary" width="3em" >Contact Number</button></td>
		                <td align="right"> <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?=$query_user->contact_number?>" pattern="^[0-9]{10,12}$" ></td>
		            </tr>
		            <tr>
		                <td class="prof_btn">
		                <button type="submit" class="btn btn-default btn" name="submit">Edit</button>
		            	</td>
		            </form>
		            </tr>
		     	</table>  		
			</div>  <!--END OF PROFILE DIV--> 
			<div id ="profile_books_carousel">
				<div id="this-carousel-id" class="carousel slide">
					<div class="carousel-inner" id="img-car">
						<div class = "item active">
							<div class = "profile_books_item">
									<?php if($query_user->user_type=='S' || $query_user->user_type=='F' ) { //if user is student?>
		            					<h4>Borrowed Books: </h4> <!--Borrowed books-->
		<?php foreach($query_book as $row){ //transaction query
			$reference_material_id=$row->reference_material_id;
			$query_ref_mat = $this->user_model->user_book_reserve($reference_material_id); //query for table reference_material
			foreach($query_ref_mat as $row2){ 
				if($row->date_returned != null) continue; // if book is returned
				if($row->waitlist_rank ==null) { // if do not have a rank on waitlist
			?>
				Title: <?=$row2->title?><br/><!--Title-->
				Author: <?=$row2->author?><br/><!--Author-->
				ISBN: <?=$row2->isbn?><br/><!--ISBN-->
				Category: <?=$row2->category?><br/><!--Category-->
				Description: <?=$row2->description?><br/><!--Description-->
				Publication: <?=$row2->publisher?> <?=$row2->publication_year?><br/><!--Publication-->
				Access Type: <?=$row2->access_type?><br/> <!--Access Type-->
				Course Code: <?=$row2->course_code?><br/> <!--Course Code-->
				<br/>
				<form method="post" accept-charset="utf-8" action="<?=base_url()."index.php/profile/cancel_transaction"?>">
					<input type="hidden" name="ref_id" value=<?=$row2->id?> />
					<input type="submit" name="cancel_reserve" id="cancel_reserve" value="Cancel Reservation"/><br/><br/>
				</form>
				<?php }//end if?>
			<?php }//end of foreach ?>
		<?php }//end of foreach?>
									<?php }else {?>
										<h1>Borrowed books:</h1><br>
											<ul>
										<li>No borrowed books</li><br>
									</ul>
									<?php } ?>
							</div>
						</div>
						<div class="item">
							<div class = "profile_books_item">
								<?php if($query_user->user_type=='S' || $query_user->user_type=='F' ) { //if user is student?>
		            					<h4>Waitlisted Books: </h4> <!--Waitlisted Books-->
		<?php foreach($query_book as $row){ //transaction query
			$reference_material_id=$row->reference_material_id;
			$query_ref_mat = $this->user_model->user_book_reserve($reference_material_id);//query for table reference_material
			foreach($query_ref_mat as $row2){ 
				if($row->waitlist_rank == null || $row->waitlist_rank <= 0) continue; // if there is no waitlist rank
			?>	BOOK ID: <?=$row2->id?><br/>
				Title: <?=$row2->title?><br/><!--Title-->
				Author: <?=$row2->author?><br/><!--Author-->
				ISBN: <?=$row2->isbn?><br/><!--ISBN-->
				Category: <?=$row2->category?><br/><!--Category-->
				Description: <?=$row2->description?><br/><!--Description-->
				Publication: <?=$row2->publisher?> <?=$row2->publication_year?><br/><!--Publication-->
				Access Type: <?=$row2->access_type?><br/> <!--Access Type-->
				Course Code: <?=$row2->course_code?><br/> <!--Course Code-->
				<br/>
				<form method="post" accept-charset="utf-8" action="http://localhost/icsls/index.php/profile/cancel_transaction">
					<input type="hidden" name="ref_id" value=<?=$row2->id?> />
					<input type="submit" name="cancel_waitlist" id="cancel_waitlist" value="Cancel Waitlist"/><br/><br/>
				</form>
			<?php } // end of foreach?>
		<?php }//end of foreach?>
									<?php }else {?>
										<h1>Waitlisted books:</h1><br>
											<ul>
										<li>Empty</li><br>
									</ul>
									<?php } ?>
								
							</div>
													
						</div>
						
					</div><!-- .carousel-inner -->
				<!--  next and previous controls here
				href values must reference the id for this carousel -->
					<a class="carousel-control left" href="#this-carousel-id" data-slide="prev">&lsaquo;</a>
					<a class="carousel-control right" href="#this-carousel-id" data-slide="next">&rsaquo;</a>
				</div><!-- .carousel -->
			</div>
	</form>
	</div> <!--End of profile description-->	

	</div> <!--End of left div-->	
</div> <!--End of content div-->

<?php $this->load->view('includes/footer'); ?>

