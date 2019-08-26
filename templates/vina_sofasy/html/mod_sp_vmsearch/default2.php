<?php

    /**
    * VirtueMart Categories Module
    */

?>
<div class="<?php echo $moduleclass_sfx; ?> sp-vmsearch" id="sp-vmsearch-<?php echo $module_id ?>">
    <form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=category&search=true&limitstart=0' ); ?>" method="get">
        <input type="hidden" name="limitstart" value="0" />
        <input type="hidden" name="option" value="com_virtuemart" />
        <input type="hidden" name="view" value="category" />
		<input type="text" name="keyword" autocomplete="off" placeholder="Type Keyword..." class="sp-vmsearch-box" value="<?php echo JRequest:: getVar('keyword') ?>" />
		<div class="search-button-wrapper">
            <button type="submit" class="search-button"><?php echo JText::_('SP_VMSEARCH_SEARCH_BUTTON') ?></button>
        </div>
    </form>
</div>


<script type="text/javascript">
    jQuery(function($){
            
            // change event
            $('#sp-vmsearch-<?php echo $module_id ?> .sp-vmsearch-categories').on('change', function(event){
                    var $name = $(this).find(':selected').attr('data-name');
                    $('#sp-vmsearch-<?php echo $module_id ?> .sp-vmsearch-category-name .category-name').text($name);

            });


            // typeahed
            $('#sp-vmsearch-<?php echo $module_id ?> .sp-vmsearch-box').typeahead({
                    items  : '<?php echo $max_search_suggest; ?>',
                    source : (function(query, process){
                            return $.post('<?php echo JURI::current() ?>', 
                                { 
                                    'module_id': '<?php echo $module_id; ?>',
                                    'char': query,
                                    'category': $('#sp-vmsearch-<?php echo $module_id ?> .sp-vmsearch-categories').val()
                                }, 
                                function (data) {
                                    return process(data);
                                },'json');
                    }),
            }); 
    });
    </script>