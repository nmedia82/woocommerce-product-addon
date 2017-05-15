<?php
/**
 * template to be loaded as thickbox to crop image
 */
 global $nmpersonalizedproduct;

 ?>
 
 <link rel="stylesheet" href="<?php echo $nmpersonalizedproduct -> plugin_meta['url']?>/lib/jcrop/css/jquery.Jcrop.css" type="text/css" />
 
<style>
#image-wrapper{
	min-width: 400px;
margin: 0 auto;
text-align: center;
}

#canvas-header, #canvas-footer{
	/*height: 30px;*/
	padding: 12px;
	background: #999;
	color: #fff;
}

#cropping-ratios-bar{
	/*float: left;
	width: 5%;*/
}
#cropping-ratios-bar ul{
	
}
ul#ratios-list, ul#ratios-list li {
  margin: 0;
  padding: 0;
}
ul#ratios-list {
  list-style-type: none;
}
#cropping-ratios-bar ul li{
	text-align: center;
border: 1px solid;
cursor: pointer;
padding: 5px;
float: left;
margin: 5px;
}

#crop-area{
	/*float: right;
	width: 95%;*/
	margin: 0 auto;
	text-aling:center;
}

.selected-ratio{
	background: #FF7700;
	color: #000;
}

#cropp-info{
	width: 75%;
margin: 0 auto;
text-align: center;
}
#cropp-info li{
	text-align: center;
border: 1px solid;
cursor: pointer;
padding: 5px;
margin: 0 auto;
display: inline;
}
</style>
 <div id="image-wrapper">
 	
 	<div id="canvas-header">
 	<div id="cropping-ratios-bar">
 		<ul id="ratios-list">
 			<?php
 			if($ratio){
 				foreach($ratio as $r){
 					echo '<li>'.$r.'</li>';
 				}
 			}
 			?>
 		</ul>
    <div style="clear:both;"></div>
 	</div>
 	</div>
 	
 	<div id="crop-area">
	 	<img id="crop-me" src="<?php echo $image_url?>" />
	 	<input type="hidden" value="<?php echo $fileid;?>" id="fileid" />
	 	
	 	<p><a javascript=":;" class="button button-primary" id="btn_cropp"><?php _e('Finish cropping', 'nm-personalizedproduct');?></a></p>
	 </div>
	 
	 <div id="canvas-footer">
	 	<ul id="cropp-info">
	 		<li id="c-x"></li>
	 		<li id="c-y"></li>
	 		<li id="c-x2"></li>
	 		<li id="c-y2"></li>
	 		<li id="c-w"></li>
	 		<li id="c-h"></li>
	 	</ul>
	 </div>
 	
 	
 </div>
 
 <script type="text/javascript">
 <!--
  jQuery(function($){

	var jcropapi;
    // How easy is this??
    var coords = '';
    $('#crop-me').Jcrop({
    		aspectRatio: eval($("#ratios-list li:first").text()),
    	 	onSelect: showCoords,
          	onChange: showCoords,
          	setSelect:   [ ($('#crop-me').width() / 2) - 70, 
                       ($('#crop-me').height() / 2) - 70, 
                       ($('#crop-me').width() / 2) + 70, 
                       ($('#crop-me').height() / 2) + 70
                     ],
          }, function(){
          	jcropapi = this;
          	$(".jcrop-holder").css("margin", "0 auto");
          });
          
    $("#ratios-list li:first").addClass("selected-ratio");
    
    $("#ratios-list li").on("click", function(){
    	
    	$("#ratios-list li").removeClass("selected-ratio");
    	$(this).addClass("selected-ratio");
    	jcropapi.setOptions({aspectRatio:eval( $(this).text())} );
    	
    });
          
  	function showCoords(c){
  		coords = c;
  		
  		$("#c-x").text('x: '+parseInt(c.x));
  		$("#c-y").text('y: '+parseInt(c.y));
  		$("#c-x2").text('x2: '+parseInt(c.x2));
  		$("#c-y2").text('y2: '+parseInt(c.y2));
  		$("#c-w").text('w: '+parseInt(c.w));
  		$("#c-h").text('h: '+parseInt(c.h));
  	}
  	
  	$("#btn_cropp").on('click', function(e){
  		e.preventDefault();
  		//console.log(coords);
  		var data = {coords: coords, image_name:'<?php echo $image_name;?>', 
  					action:'nm_personalizedproduct_crop_image', fileid: $("#fileid").val(),
  					img_w: $(".jcrop-holder").find('img:last').width(),
  					img_h: $(".jcrop-holder").find('img:last').height()}
  		$.post(nm_personalizedproduct_vars.ajaxurl, data, function(resp){
  			
  			//console.log(resp);
  			document.getElementById(resp.fileid).src = resp.cropped_image;
  			document.getElementById(resp.fileid).className = "cropped-thumb";
  			window.parent.tb_remove();
  			
  			
  		}, 'json');
  	});

  });
//-->
</script>
