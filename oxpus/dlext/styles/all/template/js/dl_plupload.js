var plupload_img_path = '@oxpus_dlext/plupload/img/';

// init the second ajax function
function parse_uploaded_images() {
	$('#dl_thumbs').fadeOut('Fast');

	$.ajax({
		url: dl_pic_url,
		type: "GET",
		success: function (data) { location.assign(dl_pic_url);}
	});
}

// Initialize the widget when the DOM is ready
$(function() {
	$("#uploader").plupload({
		// General settings
		runtimes : 'html5,html4',
		url : dl_pic_url,

		multipart_params: {
			df_id: dl_param_1,
			cat_id: dl_param_2,
			img_id: dl_param_3,
			edit_img_link: dl_param_4,
			action: 'ajax',
		},

		// User can upload no more then given number of files in one go (sets multiple_queues to false)
		max_file_count: dl_max_files,

		chunk_size: dl_max_sizes,

		filters :
		{
			// Maximum file size
			max_file_size : dl_max_sizes,
			// Specify what files to browse for
			mime_types: [{title : "Image files", extensions : "gif,png,jpg,jpeg"}],
			prevent_duplicates : true
		},

		// Rename files by clicking on their titles
		rename: true,

		// Sort files
		sortable: true,

		// Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
		dragdrop: true,

		// Views to activate
		views:
		{
			list: false,
			thumbs: true, // Show thumbs
			active: 'thumbs',
		},
	});

	// run second upload procedure after queue was uploaded
	var uploader = $("#uploader").plupload();
	uploader.bind('complete', function() {
		parse_uploaded_images();
	});

	// Handle the case when form was submitted before uploading has finished
	$('#dl_thumbs').submit(function(e) {
		// Files in queue upload them first
		if ($('#uploader').plupload('getFiles').length > 0) {

			// When all files are uploaded submit form
			$('#uploader').on('complete', function() {
				$('#dl_thumbs')[0].submit();
			});

			$('#uploader').plupload('start');
		}

		return false; // Keep the form from submitting
	});
});
