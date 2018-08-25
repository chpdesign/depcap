<?php
namespace ComposerPack\System\ORM\Types;

class File extends Type {

    protected $valid = [];

    public function valid($valid = null)
    {
        if(is_null($valid))
        {
            return $this->valid;
        }
        $this->valid = $valid;
    }

    public function blockField($model, $formid, $url)
    {
        $key = $this->key();
        $value = $model[$key];
        $valid = $this->valid();
        ob_start();
        ?>
        <div class="row">
            <?php
            $filescount = 0;
            if(!empty($value))
            {
                $file = new \SplFileInfo($value);

                // filter out directories
                try
                {
                    if($file->isFile()) {

                        // Use pathinfo to get the file extension
                        $info = pathinfo($file->getPathname());

                        // Check there is an extension and it is in the whitelist
                        if (isset($info['extension']) && (empty($valid) || isset($valid[$info['extension']]))) {
                            $file = array(
                                'filename' => $file->getFilename(),
                                'path' => str_replace(get('base_dir'), "", $info['dirname']) . '/',
                                'size' => $file->getSize(),
                                'type' => $info['extension'], // 'PDF' or 'Word'
                                'created' => date('Y-m-d H:i:s', $file->getCTime())
                            );

                            $filescount++;

                            ?>
                            <div class="col-md-12 <?php echo $this->formFieldId(); ?>item">
                            <input class=" <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" type="hidden" name="<?php echo $key; ?>"
                                   value="<?php echo $file['path'] . $file['filename']; ?>" <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>/>
                            <?php if (!($this->disabled())) { ?>
                                <span class="action">
                            <span class="btn btn-danger pull-left remove"><i class="glyphicon glyphicon-remove"></i></span>
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
                }
                catch (\Exception $e)
                {
                }
            }
            ?>
            <script id="<?php echo $this->formFieldId(); ?>clone" type="text/template">
                <div class="col-md-12 <?php echo $this->formFieldId(); ?>item">
                    <input type="hidden" name="<?php echo $key; ?>" />
                    <div class="progress">
                        <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="min-width: 4em;">

                        </div>
                    </div>
                    <div class="finished hidden">
					<span class="action">
						<span class="btn btn-danger pull-left remove"><i class="glyphicon glyphicon-remove"></i></span>
					</span>
                        <a href="" download class="btn btn-link"><i class="fa fa-file-o"></i> <span class="filename"></span></a>
                    </div>
                </div>
            </script>
            <?php if(!($this->disabled())){ ?>
                <div class="col-md-2 col-lg-3 <?php echo $filescount > 0 ? 'hidden' : ''; ?> upload-btn">
                    <button type="button" class="btn btn-site btn-file btn-block">
                        <input type="file" title="Hozzáadás"/>
                        <i class="fa fa-file-o"></i> Hozzáadás
                    </button>
                </div>
            <?php } else if($filescount == 0) { ?>
                <div class="col-md-2">
                    <div class="help-block">Nincsen fájl feltöltve!</div>
                </div>
            <?php } ?>
            <?php
            ?>
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

                    $(_this).parents('.upload-btn').addClass('hidden');

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

    public function __construct(array $field = [])
    {
        parent::__construct($field);
    }

    public function icon($type)
    {
        if(empty($type))
            return self::$icons['file'];
        $type = strtolower($type);
        if(isset(self::$extensions[$type]) && isset(self::$icons[self::$extensions[$type]]))
            return self::$icons[self::$extensions[$type]];
        return self::$icons['file'];
    }

    public function iconType($type)
    {
        if(empty($type))
            return self::$extensions['file'];
        $type = strtolower($type);
        if(isset(self::$extensions[$type]))
            return self::$extensions[$type];
        return self::$extensions['file'];
    }

    // https://github.com/spatie/font-awesome-filetypes
    // nyomán... php-sítva...
    protected static $icons = [
        'image' => 'fa-file-image-o',
        'pdf' => 'fa-file-pdf-o',
        'word' => 'fa-file-word-o',
        'powerpoint' => 'fa-file-powerpoint-o',
        'excel' => 'fa-file-excel-o',
        'audio' => 'fa-file-audio-o',
        'video' => 'fa-file-video-o',
        'zip' => 'fa-file-zip-o',
        'code' => 'fa-file-code-o',
        'text' => 'fa-file-text-o',
        'file' => 'fa-file-o'
    ];

    protected static $extensions = [
        'gif' => 'image',
        'jpeg' => 'image',
        'jpg' => 'image',
        'png' => 'image',

        'pdf' => 'pdf',

        'doc' => 'word',
        'docx' => 'word',

        'ppt' => 'powerpoint',
        'pptx' => 'powerpoint',

        'xls' => 'excel',
        'xlsx' => 'excel',

        'aac' => 'audio',
        'mp3' => 'audio',
        'ogg' => 'audio',

        'avi' => 'video',
        'flv' => 'video',
        'mkv' => 'video',
        'mp4' => 'video',

        'gz' => 'zip',
        'zip' => 'zip',

        'css' => 'code',
        'html' => 'code',
        'js' => 'code',

        'txt' => 'text',

        'file' => 'file'
    ];

}