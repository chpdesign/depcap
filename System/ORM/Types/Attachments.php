<?php
namespace ComposerPack\System\ORM\Types;

class Attachments extends File {

    public function defaultValue($model)
    {
        $e = parent::defaultValue($model);
        if(empty($e))
            return [];
        else
            return parent::defaultValue($model);
    }

    public function listField($model, $url)
    {
        if(isset($model[$this->key()]))
            return $model[$this->key()];
        return null;
    }

    protected $valid = [];

    public function valid($valid = null)
    {
        if($valid == null)
        {
            return $this->valid;
        }
        $this->valid = $valid;
    }

    public function processData(&$model, $data, $table)
    {
        $key = $this->key();
        if(isset($data[$key]))
        {
            $r = [];
            foreach ($data[$key] as $index => $file)
            {
                if(strpos($file, get('base_dir')) === 0)
                {
                    $_file = substr($file, strlen(get('base_dir').'/'));
                    $r[$index] = $_file;
                }
                else
                {
                    $r[$index] = $file;
                }
            }
            return $r;
        }
        else if(isset($model[$key]))
            return $model[$key];
        return [];
    }

    public function blockField($model, $formid, $url)
    {
        $valid = $this->valid();
        $key = $this->key();
        if(isset($model[$key]))
            $value = $model[$key];
        else
            $value = $this->defaultValue($model);
		ob_start();
		?>
		<div class="row">
		<?php
        $filescount = 0;
		if(!empty($value))
		{
			foreach($value as $id => $_file)
			{
				$file = new \SplFileInfo($_file);

				// filter out directories
                try
                {
                    if (!$file->isFile()) continue;
                }
                catch (\Exception $e)
                {
                    continue;
                }
				
				// Use pathinfo to get the file extension
				$info = pathinfo($file->getPathname());
				
				// Check there is an extension and it is in the whitelist
				if(isset($info['extension']) && (empty($valid) || isset($valid[$info['extension']])))
				{
					$file = array(
						'filename' => $file->getFilename(),
						'path' => substr($info['dirname'], strlen(get('base_dir'))).'/',
						'size' => $file->getSize(),
						'type' => $info['extension'], // 'PDF' or 'Word'
						'created' => date('Y-m-d H:i:s', $file->getCTime())
					);
				}
				else continue;
                $filescount++;
				
				?><div class="col-md-12 <?php echo $this->formFieldId(); ?>item">
					<input type="hidden" name="<?php echo $key; ?>[]" value="<?php echo $file['path'].$file['filename']; ?>" <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?> class=" <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>"/>
					<?php if(!($this->disabled())) { ?>
					<span class="action">
						<span class="btn btn-danger pull-left remove <?php echo 'can-disable '.($this->disabled() ? 'field-disabled' : ''); ?>"><i class="glyphicon glyphicon-remove"></i></span>
					</span>
					<?php } ?>
					<a href="<?php echo $url.'/download?'; ?><?php echo http_build_query(['file' => $file['path'].$file['filename']]); ?>" download="<?php echo $file['filename']; ?>" class="btn btn-link"
                       <?php
                       if($this->iconType(strtolower($file['type'])) == 'image')
                       {
                       ?>rel="popover" data-template='<div class="popover" role="tooltip"><h3 class="popover-title"></h3><div class="popover-content"></div></div>' data-placement="bottom" data-title="" data-content='<img style="max-width: 200px; max-height: 200px;" src="<?php echo $url.'/download?'; ?><?php echo http_build_query(['file' => $file['path'].$file['filename']]); ?>"/>'<?php
                    }
                    ?>
                    >
                        <i class="fa <?php echo $this->icon(strtolower($file['type'])); ?>"></i>
                        <span data-filename="<?php echo $file['path'].$file['filename']; ?>" class="filename"><?php echo $file['filename']; ?></span>
                    </a>
				</div><?php
			}
		}
		?>
		<script id="<?php echo $this->formFieldId(); ?>clone" type="text/template">
			<div class="col-md-12 <?php echo $this->formFieldId(); ?>item">
				<input type="hidden" name="<?php echo $key; ?>[]" />
				<div class="progress">
					<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="min-width: 4em;">
						
					</div>
				</div>
				<div class="finished hidden">
					<span class="action">
						<span class="btn btn-danger pull-left remove <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>"><i class="glyphicon glyphicon-remove"></i></span>
					</span>
					<a href="" download class="btn btn-link"><i class="fa fa-file-o"></i> <span class="filename"></span></a>
				</div>
			</div>
		</script>
		<?php if(!($this->disabled())){ ?>
		<div class="col-md-2 col-lg-3">
			<button type="button" class="btn btn-site btn-file btn-block">
		    	<input type="file" title="Hozzáadás" class="<?php echo 'can-disable '.($this->disabled() ? 'field-disabled' : ''); ?>"/>
		        <i class="fa fa-file-file-o"></i> Hozzáadás
		    </button>
	    </div>
	    <?php } else if($filescount == 0) { ?>
        <div class="col-md-2">
		    <div class="help-block">Nincsenek feltöltött fájlok</div>
        </div>
        <?php } ?>
		    <div class="clearfix"></div>
		</div>
		<script type="text/javascript">
		(function(){
			var icons = <?php echo json_encode(self::$icons); ?>;
			var extensions = <?php echo json_encode(self::$extensions); ?>;
			$(document).on('change', '.<?php echo $this->formFieldId(); ?> .btn-file :input[type="file"]', function(evt){
				var _this = this;
		  		var tgt = evt.target || window.event.srcElement,
		        files = tgt.files;
		  		
		  		
		  		
		  		for (var index = 0; index < files.length; index++)
		  		{
		  			
		  			var $fileTemplate = $($('#<?php echo $this->formFieldId(); ?>clone').text().trim());
	  				$('#<?php echo $this->formFieldId(); ?>clone').before($fileTemplate);
		  		
			  		var form = new FormData();
				    var file = this.files[index]; 
				    form.append('file', file);
				    this.value = '';	
	
					var $this = $(this);
					var url = '<?php echo $url.'/tempfolder'; ?>';
					
					$.ajax({
				    	xhr: function()
						{
				    	    var xhr = new window.XMLHttpRequest();
							//Upload progress
							xhr.upload.addEventListener("progress", function(evt){
								if (evt.lengthComputable) {  
									var percentComplete = evt.loaded / evt.total;
									$('.progress-bar',$fileTemplate).text((Math.round(percentComplete * 10000) / 100)+'%');
									$('.progress-bar',$fileTemplate).css({width: (Math.round(percentComplete * 10000) / 100)+'%'});
								}
							}, false); 
							//Download progress
							xhr.addEventListener("progress", function(evt){
								if (evt.lengthComputable) {  
			    	        		var percentComplete = evt.loaded / evt.total;
			    	        		$('.progress-bar',$fileTemplate).text(percentComplete+'%');
									$('.progress-bar',$fileTemplate).css({width: percentComplete+'%'});
								}
							}, false); 
							return xhr;
						},
						dataType: 'json',  // what to expect back from the PHP script, if anything
						cache: false,
						contentType: false,
						url: url,
						processData: false,
						data: form,                         
						type: 'post',
						success: function(response){
							
							if(response.result)
							{
								$('.progress', $fileTemplate).addClass('hidden');
								$('.finished', $fileTemplate).removeClass('hidden');
								
				  				var filename = response.result.split(/(\\|\/)/g).pop();
				  				var extension = filename.split('.').pop().toLowerCase();
				  				
				  				if(extensions[extension] && icons[extensions[extension]]) {
				  					$('.fa', $fileTemplate).attr('class', '').addClass('fa').addClass(icons[extensions[extension]]);

                                    if(extensions[extension] == 'image')
                                        $('a', $fileTemplate).attr('rel','popover').data('template', '<div class="popover" role="tooltip"><h3 class="popover-title"></h3><div class="popover-content"></div></div>').data('placement','bottom').data('title','').data('content', '<img style="max-width: 200px; max-height: 200px;" src="'+'<?php echo $url.'/download'; ?>?file='+response.result+'"/>').popover({trigger: "hover", html: true});
                                }
				  				else
				  					$('.fa', $fileTemplate).attr('class', '').addClass('fa fa-file-o');
				  				$('.filename', $fileTemplate).text(filename);
				  				$('.filename', $fileTemplate).data('filename', response.result);
				  				
				  				$('[download]', $fileTemplate).attr('href', '<?php echo $url.'/download'; ?>?file=' + response.result);
				  				$('[download]', $fileTemplate).attr('download', response.result.split(/(\\|\/)/g).pop());

				  				$(':input', $fileTemplate).val(response.result);
							}
			  				
						}
					})
		  		
		  		}
			});
			$(document).on('click', '.<?php echo $this->formFieldId(); ?> .remove', function(){
			    if($(this).hasClass('field-disabled') || $(this).hasClass('disabled'))
			        return false;
                var $this = $(this);
                var $parent = $this.parents('.<?php echo $this->formFieldId(); ?>item');

                var filename = $('.filename', $parent).data('filename');
                var data = {remove: filename};
                var url = '<?php echo $url.'/tempfolder'; ?>';

                $.ajax({
                    url: url,
                    data: data,
                    type: 'POST',
                    dataType: 'JSON',
                    success: function(response){
                        if(response.result === filename) {
                            $('.<?php echo $this->formFieldId(); ?> .btn-file :input[type="file"]').parents('.upload-btn').removeClass('hidden');
                            $parent.remove();
                        }
                    }
                });

                return false;
			});
            $('.<?php echo $this->formFieldId(); ?> [data-toggle="popover"][data-trigger="hover"]').popover({trigger: "hover", html: true});
		})();
		</script>
		<?php
		return ob_get_clean();
	}

}