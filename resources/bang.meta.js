// import { tat } from 'tat-js';

var bangMeta = {

	init: function() {
		
		jQuery(document).ready(function() { 
			jQuery('.js-sortable tbody').sortable({ handle: ".handle" });
		});
		
		this.metaImgListeners();
		
		var tables = document.querySelectorAll('#postcustomstuff tbody');
		if (tables) {
			Array.from(tables).forEach(table => {
				table.addEventListener('rowAdded', bangMeta.metaImgListeners);	
			});
		}
		
	},
	
	metaImgListeners: function() {
		
		let handles = document.querySelectorAll('.js-upload-meta-img');
		if (handles) {
			Array.from(handles).forEach(handle => {
				handle.addEventListener('click', bangMeta.metaImgUpload);	
			});
		}
		
		let handels = document.querySelectorAll('.js-delete-meta-img');
		if (handels) {
			Array.from(handels).forEach(handel => {
				handel.addEventListener('click', bangMeta.metaImgDelete);	
			});
		}
		
	},
	
	metaImgUpload: function(event) {
				
		event.preventDefault();
    
	    var frame;
	    var metaBox = this.closest('.meta-img-field');
	    var addImgLink = metaBox.querySelector('.js-upload-meta-img');
	    var delImgLink = metaBox.querySelector( '.js-delete-meta-img');
	    var imgContainer = metaBox.querySelector( '.meta-img-container');
	    var imgIdInput = metaBox.querySelector( '.meta-img-id' );
	    var postID = metaBox.dataset.postid; 
	    var type = (metaBox.dataset.type=='video' ? 'video' : 'image');

	    // If the media frame already exists, reopen it.
	    if ( frame ) {
			frame.open();
			return;
	    }
	    
	    // Create a new media frame
	    frame = wp.media({
			title: 'Select or Upload Media',
	        button: {
				text: 'Use this media'
	        },
			multiple: false,  // Set to true to allow multiple files to be selected
			library: {
	            type: type,
	            post_parent: postID // the current post id
	        }
	    });
	
	    frame.on( 'select', function() {
	      
			// Get media attachment details from the frame state
			var attachment = frame.state().get('selection').first().toJSON();

			if (type=='video') {
				imgContainer.innerHTML = '<video src="'+attachment.url+'" alt="" style="max-height:50px; width: auto; margin-right: 8px;" controls></video>';	
			} else {
				imgContainer.innerHTML = '<img src="'+attachment.url+'" alt="" style="max-height:50px; width: auto; margin-right: 8px;">';
			}
			
			imgIdInput.value = attachment.id;
			
			addImgLink.classList.add( 'hidden' );
			delImgLink.classList.remove( 'hidden' );
	    });
	    
	    frame.open();
	
	},
	
	metaImgDelete: function(event) {
	
		event.preventDefault();

		var metaBox = this.closest('.meta-img-field');
	    var addImgLink = metaBox.querySelector('.js-upload-meta-img');
	    var delImgLink = metaBox.querySelector( '.js-delete-meta-img');
	    var imgContainer = metaBox.querySelector( '.meta-img-container');
	    var imgIdInput = metaBox.querySelector( '.meta-img-id' );
	    
	    imgContainer.innerHTML = '';
	
	    addImgLink.classList.remove( 'hidden' );
	    delImgLink.classList.add( 'hidden' );
	
	    imgIdInput.value = '';
		
	}
	
}

bangMeta.init();
