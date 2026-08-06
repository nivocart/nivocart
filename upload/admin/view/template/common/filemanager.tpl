<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<link rel="stylesheet" type="text/css" href="view/stylesheet/filemanager.css" />
<link rel="stylesheet" type="text/css" href="view/javascript/jquery/ui/jquery-ui-1.14.2.min.css" />
<link rel="stylesheet" type="text/css" href="view/javascript/jquery/jstree/themes/default/style.min.css" />
<link rel="stylesheet" type="text/css" href="view/javascript/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" />
<link rel="stylesheet" type="text/css" href="view/stylesheet/font-awesome.min.css" />

<script type="text/javascript" src="view/javascript/jquery/jquery-3.7.1.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery/jquery-migrate-3.6.0.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-1.14.2.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery/jstree/jstree.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery/jstree/js-cookie-3.0.8.min.js"></script>
<script type="text/javascript" src="view/javascript/plupload/js/plupload.full.min.js"></script>
<script type="text/javascript" src="view/javascript/plupload/js/jquery.ui.plupload/jquery.ui.plupload.min.js"></script>
</head>
<body>
<div id="container">
  <div id="file-menu">
    <a id="create" class="filemanager-button ripple" style="background-image: url('view/image/filemanager/folder.png');"><?php echo $button_folder; ?></a>
    <a id="delete" class="filemanager-button ripple" style="background-image: url('view/image/filemanager/edit-delete.png');"><?php echo $button_delete; ?></a>
    <a id="move" class="filemanager-button ripple" style="background-image: url('view/image/filemanager/edit-cut.png');"><?php echo $button_move; ?></a>
    <a id="copy" class="filemanager-button ripple" style="background-image: url('view/image/filemanager/edit-copy.png');"><?php echo $button_copy; ?></a>
    <a id="rename" class="filemanager-button ripple" style="background-image: url('view/image/filemanager/edit-rename.png');"><?php echo $button_rename; ?></a>
    <a id="upload" class="filemanager-button ripple" style="background-image: url('view/image/filemanager/upload-plus.png');"><?php echo $button_upload; ?></a>
    <a id="refresh" class="filemanager-button ripple" style="background-image: url('view/image/filemanager/refresh.png');"><?php echo $button_refresh; ?></a>
    <a id="information" class="filemanager-button ripple hide-phone" style="background-image: url('view/image/filemanager/information.png');"><?php echo $button_info; ?></a>
  </div>
  <div id="column-right"></div>
  <div id="column-left"></div>
  <div id="jstree" class="filter">
    <input type="text" name="filter" id="filter" /><img src="view/image/filemanager/filter.png" alt="" />
  </div>
  <div id="toolset">
    <button id="btnExpand" class="btn"><?php echo $button_expand; ?></button>
    <button id="btnCollapse" class="btn"><?php echo $button_collapse; ?></button>
    <button id="btnTextView" class="btn"><?php echo $button_view_text; ?></button>
    <button id="btnListView" class="btn"><?php echo $button_view_list; ?></button>
    <button id="btnThumbView" class="btn"><?php echo $button_view_thumb; ?></button>
  </div>
  <span class="branding hide-phone">
    <a onclick="window.open('https://nivocart.org');" title="NivoCart" style="text-decoration:none;">NivoCart</a>
  </span>
  <div id="information-dialog" style="display:none;"></div>
</div>

<script type="text/javascript"><!--
// ============================================================
// NivoCart Popup File Manager - JS rewrite for jQuery 3.7.1
// Changes: jquery.tree 0.9.9 → jsTree 3.x, jquery.cookie → js-cookie 3.x
// .delegate() → .on(), $.tree.focused() → jstree instance pattern
// ============================================================

$(document).ready(function() {
    // ----------------------------------------------------------
    // HELPER: get the jsTree instance on #column-left
    // CHANGE: $.tree.focused() no longer exists in jsTree 3.x.
    //         Use $('#column-left').jstree(true) to get the instance.
    //         Wrapped in a helper so we only write it once.
    // ----------------------------------------------------------
    function getTree() {
        return $('#column-left').jstree(true);
    }

    // ----------------------------------------------------------
    // JSTREE INIT
    // CHANGE: Old API was $('#column-left').tree({ ... }) with its own
    //         config format. jsTree 3.x uses $('#column-left').jstree({ ... })
    //         with a completely different option structure.
    //
    //   - 'plugins' array replaces the old plugins object. We use
    //     'state' (replaces the old cookie plugin) and 'types'.
    //   - 'state' plugin uses js-cookie internally when available,
    //     or falls back to localStorage. It handles open/selected
    //     state persistence automatically — no manual $.cookie() calls needed.
    //   - 'core.data' replaces the old 'data' block. It receives
    //     a function that jsTree calls when it needs to load a node.
    //     'node.id === "#"' means the root request.
    //   - The old 'callback.beforedata', 'callback.onselect', and
    //     'callback.onopen' are replaced by jsTree events (below).
    //   - 'types' config is largely the same concept but uses the
    //     jsTree 3.x key format.
    //   - The old 'ui.theme_name' / 'ui.animation' options are gone;
    //     jsTree 3.x uses its own bundled theme (or a custom one via CSS).
    // ----------------------------------------------------------
    $('#column-left').jstree({
        plugins: ['state', 'types'],

        // 'state' plugin — replaces old cookie plugin.
        // CHANGE: Old code used jquery.cookie directly via { plugins: { cookie: {} } }.
        //         jsTree 3.x 'state' plugin manages open/selected state itself.
        //         'key' is the localStorage/cookie key used to persist state.
        state: {
            key: 'filemanager_tree_state'
        },

        core: {
            // CHANGE: 'data' is now a function jsTree calls per node.
            //         'node' is the node being opened; node.id === '#' means root.
            //         Call callback(data) with the child nodes array.
            //         Node format: { id, text, children (bool), li_attr: { directory } }
            //         li_attr replaces the old 'attributes' key for custom HTML attributes.
            data: function(node, callback) {
                var directory = (node.id === '#') ? '' : node.li_attr.directory;
                $.ajax({
                    url: 'index.php?route=common/filemanager/directory&token=<?php echo $token; ?>',
                    type: 'post',
                    data: { directory: directory },
                    dataType: 'json',
                    success: function(json) {
                        // CHANGE: jsTree 3.x expects nodes in this format.
                        //         'children: true' tells jsTree the node is expandable
                        //         without pre-loading its children.
                        //         Adjust the mapping below to match your server's JSON shape.
                        var nodes = [];
                        if (node.id === '#') {
                            // Root node — mirrors the old 'beforedata' static root
                            nodes = [{
                                id: 'top',
                                text: 'image',
                                children: true,
                                li_attr: { directory: '' }
                            }];
                        } else {
                            // CHANGE: Map your server response to jsTree 3.x node objects.
                            //         Replace json[i].data / json[i].attributes with whatever
                            //         your /directory endpoint actually returns.
                            $.each(json, function(i, item) {
                                nodes.push({
                                    text: item.data || item.name,
                                    children: true,
                                    li_attr: { directory: item.attributes ? item.attributes.directory : item.directory }
                                });
                            });
                        }
                        callback(nodes);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            },

            // CHANGE: Allows HTML in node labels if your directory names need it.
            //         Set false if your names are plain text (safer).
            check_callback: true
        },

        // CHANGE: 'types' config same concept as before but uses jsTree 3.x structure.
        //         '#' = root constraints, 'default' = all other nodes.
        types: {
            '#': {
                max_children: -1,
                max_depth: -1,
                valid_children: 'all'
            },
            'default': {
                valid_children: 'all'
            }
        }
    });

    // ----------------------------------------------------------
    // EVENT: node selected → load files in #column-right
    // CHANGE: Old API used callback.onselect inside the tree config.
    //         jsTree 3.x fires a jQuery event: 'select_node.jstree'
    //         'data.node' is the selected node object.
    //         $(NODE).attr('directory') becomes node.li_attr.directory
    // ----------------------------------------------------------
    $('#column-left').on('select_node.jstree', function(e, data) {
        var directory = data.node.li_attr.directory;

        // Keep window.dr in sync (used by the upload dialog)
        window.dr = directory;

        $.ajax({
            url: 'index.php?route=common/filemanager/files&token=<?php echo $token; ?>',
            type: 'post',
            data: 'directory=' + encodeURIComponent(directory),
            dataType: 'json',
            success: function(json) {
                var html = '<div>';
                if (json) {
                    if (json.length === 0) {
                        html += '<div class="feedback"><?php echo $text_no_file_found; ?></div>';
                    } else {
                        for (var i = 0; i < json.length; i++) {
                            html += '<a file="' + json[i]['file'] + '" style="float:left;" title="' + json[i]['filename'] + '">'
                                  + '<img src="' + json[i]['image'] + '" title="" alt="" />'
                                  + '<span class="fileName">' + (json[i]['filename'].length > 16 ? json[i]['filename'].substr(0, 16) + '..' : json[i]['filename']) + '</span>'
                                  + '<span class="fileSize">' + json[i]['size'] + '</span>'
                                  + '<input type="hidden" name="image" value="' + json[i]['file'] + '" /></a>';
                        }
                    }
                }
                html += '</div>';
                $('#column-right').html(html);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });

    // ----------------------------------------------------------
    // EVENT: node opened → restore previously selected branch
    // CHANGE: Old callback.onopen used $.cookie('selected') and
    //         $.tree.focused() / memTree.select_branch().
    //         jsTree 3.x 'state' plugin restores state automatically,
    //         so manual cookie reading is no longer needed here.
    //         The event is kept only to set the 'directory' attribute
    //         on child <li> elements if your old code relied on that
    //         for id derivation. If you don't need that, remove this block.
    // ----------------------------------------------------------
    $('#column-left').on('after_open.jstree', function(e, data) {
        // CHANGE: Old code derived element IDs from directory names by stripping
        //         slashes and spaces. jsTree 3.x manages its own node IDs.
        //         If downstream code depended on those derived IDs, revisit.
        //         Otherwise this event handler can be left empty or removed.
    });

    // ----------------------------------------------------------
    // FILE SELECTION (single click) — unchanged in behaviour
    // CHANGE: .delegate() is removed in jQuery 3.x.
    //         Replace with .on(event, selector, handler) — same effect.
    // ----------------------------------------------------------
    $('#column-right').on('click', 'a', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            $('#column-right a').removeClass('selected');
            $(this).addClass('selected');
        }
    });

    // ----------------------------------------------------------
    // FILE SELECTION (double click → insert into CKEditor or field)
    // CHANGE: .delegate() → .on() as above. Logic unchanged.
    // ----------------------------------------------------------
    $('#column-right').on('dblclick', 'a', function() {
        <?php if ($fckeditor !== false) { ?>
        window.opener.CKEDITOR.tools.callFunction(<?php echo $fckeditor; ?>, '<?php echo $directory; ?>' + $(this).find('input[name=\'image\']').attr('value'));
        self.close();
        <?php } else { ?>
        parent.$('#<?php echo $field; ?>').attr('value', 'data/' + $(this).find('input[name=\'image\']').attr('value'));
        parent.$('#dialog').dialog('close');
        parent.$('#dialog').remove();
        <?php } ?>
    });

    // ----------------------------------------------------------
    // TOOLSET BUTTONS
    // CHANGE: jQuery UI button 'icons' option key changed from 'primary'
    //         to 'icon' in jQuery UI 1.12+. Update all icon declarations.
    //         Chain syntax preserved but each .button() now uses { icon: '...' }.
    // ----------------------------------------------------------
    $('#btnExpand').button({ icon: 'ui-icon-plus' });
    $('#btnCollapse').button({ icon: 'ui-icon-minus' });
    $('#btnTextView').button({ icon: 'ui-icon-pencil' });
    $('#btnListView').button({ icon: 'ui-icon-grip-dotted-horizontal' });
    $('#btnThumbView').button({ icon: 'ui-icon-image' });

    // ----------------------------------------------------------
    // EXPAND ALL
    // CHANGE: $.tree.focused() → getTree(); old open_all(node) →
    //         jsTree 3.x: instance.open_all(node_or_selector)
    //         instance.refresh() signature differs; open_all handles it.
    // ----------------------------------------------------------
    $('#btnExpand').on('click', function() {
        getTree().open_all('#top');
    });

    // ----------------------------------------------------------
    // COLLAPSE ALL
    // CHANGE: same pattern as expand. close_all() works the same way.
    // ----------------------------------------------------------
    $('#btnCollapse').on('click', function() {
        getTree().close_all('#top');
    });

    // ----------------------------------------------------------
    // VIEW BUTTONS — logic unchanged, no API changes needed
    // ----------------------------------------------------------
    $('#btnTextView').on('click', function() {
        $('#column-right a img').hide();
        $('#column-right a').each(function() {
            $('span.fileSize').hide();
            $(this).css({ width: '30%', height: '20px', padding: '0', float: 'left', 'text-align': 'left' });
            $('span.fileName').css({ 'margin-top': '0', 'margin-left': '15px', 'text-decoration': 'none' });
        });
    });

    $('#btnListView').on('click', function() {
        $('#column-right a img').show().each(function() {
            $(this).css({ width: '35px', height: '35px', float: 'left', padding: '3px', 'text-align': 'center' });
        });
        $('#column-right a').each(function() {
            $('span.fileSize').hide();
            $(this).css({ width: '30%', height: '40px', padding: '0', float: 'left', 'text-align': 'center' });
            $('span.fileName').css({ 'margin-top': '0', 'margin-left': '0', 'text-decoration': 'none' });
        });
    });

    $('#btnThumbView').on('click', function() {
        $('#column-right a img').show().each(function() {
            $(this).css({ width: 'auto', height: 'auto', float: 'none', padding: '0' });
        });
        $('#column-right a').each(function() {
            $('span.fileSize').show();
            $(this).css({ width: 'auto', height: 'auto', padding: '5px', margin: '5px', float: 'left', 'text-align': 'center' });
            $('span.fileName').css({ 'margin-top': '0', 'margin-left': '0', 'text-decoration': 'none' });
        });
    });

    // ----------------------------------------------------------
    // FILTER — unchanged, no API changes needed
    // ----------------------------------------------------------
    $('#filter').on('keyup', function() {
        var filter = $(this).val();
        $('#column-right a').each(function() {
            var text = $(this).text().split('.')[0];
            $(this).css('display', text.indexOf(filter) > -1 ? 'inline-block' : 'none');
        });
    });

    // ----------------------------------------------------------
    // CREATE FOLDER
    // CHANGE: $.tree.focused() → getTree()
    //         tree.selected is now the selected node ID string in jsTree 3.x.
    //         Get the node object via getTree().get_node(tree.get_selected()[0])
    //         to access li_attr.directory.
    //         tree.refresh(node) → tree.refresh_node(nodeId)
    // ----------------------------------------------------------
    $('#create').on('click', function() {
        var tree = getTree();
        var selectedId = tree.get_selected()[0];

        if (selectedId) {
            var node = tree.get_node(selectedId);
            $('#dialog').remove();

            var html = '<div id="dialog">'
                     + '<?php echo $entry_folder; ?> <input type="text" name="name" value="" /><br /><br />'
                     + '<input type="button" value="<?php echo $button_submit; ?>" />'
                     + '</div>';
            $('#column-right').prepend(html);

            $('#dialog').dialog({ title: '<?php echo $button_folder; ?>', resizable: false });

            $('#dialog input[type="button"]').on('click', function() {
                $.ajax({
                    url: 'index.php?route=common/filemanager/create&token=<?php echo $token; ?>',
                    type: 'post',
                    data: 'directory=' + encodeURIComponent(node.li_attr.directory) + '&name=' + encodeURIComponent($('#dialog input[name="name"]').val()),
                    dataType: 'json',
                    success: function(json) {
                        if (json.success) {
                            $('#dialog').remove();
                            // CHANGE: tree.refresh(node) → tree.refresh_node(nodeId)
                            tree.refresh_node(selectedId);
                        } else {
                            alert(json.error);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            });
        } else {
            alert('<?php echo $error_directory; ?>');
        }
    });

    // ----------------------------------------------------------
    // DELETE (file or folder)
    // CHANGE: $.tree.focused() → getTree()
    //         tree.selected → tree.get_selected()[0]
    //         tree.prev(node) → tree.get_prev_dom(nodeId) returns a jQuery obj;
    //           use .attr('id') to get the ID for select_node.
    //         tree.parent(node) → tree.get_parent(nodeId)
    //         tree.refresh(node) → tree.refresh_node(nodeId)
    //         tree.select_branch(id) → tree.select_node(id)
    //         $(tree.selected).attr('directory') → node.li_attr.directory
    //         $('#column-left a.clicked') pattern gone; use get_node() instead.
    // ----------------------------------------------------------
    $('#delete').on('click', function() {
        var tree = getTree();
        var selectedId = tree.get_selected()[0];
        var path = $('#column-right a.selected').attr('file');

        // Get selected folder name for the dialog
        var fldr = selectedId ? tree.get_node(selectedId).text : '';

        if (path === undefined) {
            // Delete folder
            $('#dialog').remove();

            var html = '<div id="dialog">'
                     + '<p><?php echo $text_folder_action; ?><span style="font-weight:bold;"> "' + fldr + '"</span><br />'
                     + '<?php echo $text_folder_content; ?><br /><br />'
                     + '<span style="font-weight:bold; color:Crimson;"><?php echo $text_confirm; ?></span></p>'
                     + '</div>';
            $('#column-right').prepend(html);

            $('#dialog').dialog({
                resizable: true,
                height: 230,
                width: 400,
                modal: true,
                title: '<?php echo $text_folder_delete; ?>',
                buttons: {
                    "<?php echo $text_yes_delete; ?>": function() {
                        if (selectedId) {
                            var node = tree.get_node(selectedId);
                            $.ajax({
                                url: 'index.php?route=common/filemanager/delete&token=<?php echo $token; ?>',
                                type: 'post',
                                data: 'path=' + encodeURIComponent(node.li_attr.directory),
                                dataType: 'json',
                                success: function(json) {
                                    if (json.success) {
                                        // CHANGE: tree.prev / tree.parent / tree.select_branch
                                        //         replaced with jsTree 3.x equivalents
                                        var prevId = tree.get_prev_dom(selectedId, true);
                                        var parentId = tree.get_parent(selectedId);
                                        tree.refresh_node(parentId);
                                        if (prevId) tree.select_node(prevId);
                                    }
                                    if (json.error) alert(json.error);
                                },
                                error: function(xhr, ajaxOptions, thrownError) {
                                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                                }
                            });
                        }
                        $(this).dialog('close');
                    },
                    "<?php echo $text_no_cancel; ?>": function() {
                        $(this).dialog('close');
                    }
                }
            });

        } else if (path) {
            // Delete file
            var file = path.substring(path.lastIndexOf('/') + 1).toLowerCase();
            $('#dialog').remove();

            var html = '<div id="dialog">'
                     + '<p><?php echo $text_file_action; ?> <span style="font-weight:bold;"> "' + file + '"</span><br /><br />'
                     + '<span style="font-weight:bold; color:Crimson;"><?php echo $text_confirm; ?></span></p>'
                     + '</div>';
            $('#column-right').prepend(html);

            $('#dialog').dialog({
                resizable: false,
                height: 230,
                width: 400,
                modal: true,
                title: '<?php echo $text_file_delete; ?>',
                buttons: {
                    "<?php echo $text_yes_delete; ?>": function() {
                        $.ajax({
                            url: 'index.php?route=common/filemanager/delete&token=<?php echo $token; ?>',
                            type: 'post',
                            data: 'path=' + path,
                            dataType: 'json',
                            success: function(json) {
                                if (json.success) {
                                    // CHANGE: tree.select_branch(tree.selected) → select_node to reload the file list
									if (selectedId) {
										tree.deselect_node(selectedId);
										tree.select_node(selectedId);
									}
                                }
                                if (json.error) alert(json.error);
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                            }
                        });
                        $(this).dialog('close');
                    },
                    "<?php echo $text_no_cancel; ?>": function() {
                        $(this).dialog('close');
                    }
                }
            });
        }
    });

    // ----------------------------------------------------------
    // MOVE
    // CHANGE: $.tree.focused() → getTree(); tree.selected → get_selected()[0]
    //         $(tree.selected).attr('directory') → node.li_attr.directory
    //         tree.select_branch('#top') → tree.select_node('top')
    //         tree.refresh(node) → tree.refresh_node(nodeId)
    // ----------------------------------------------------------
    $('#move').on('click', function() {
        $('#dialog').remove();

        var html = '<div id="dialog">'
                 + '<?php echo $entry_move; ?> <select name="to"></select><br /><br />'
                 + '<input type="button" value="<?php echo $button_submit; ?>" />'
                 + '</div>';
        $('#column-right').prepend(html);

        $('#dialog').dialog({ title: '<?php echo $button_move; ?>', resizable: false });
        $('#dialog select[name="to"]').load('index.php?route=common/filemanager/folders&token=<?php echo $token; ?>');

        $('#dialog input[type="button"]').on('click', function() {
            var tree = getTree();
            var selectedId = tree.get_selected()[0];
            var path = $('#column-right a.selected').find('input[name="image"]').attr('value');
            var to = $('#dialog select[name="to"]').val();

            if (path) {
                $.ajax({
                    url: 'index.php?route=common/filemanager/move&token=<?php echo $token; ?>',
                    type: 'post',
                    data: 'from=' + encodeURIComponent(path) + '&to=' + encodeURIComponent(to),
                    dataType: 'json',
                    success: function(json) {
                        if (json.success) {
                            $('#dialog').remove();
							if (selectedId) {
								tree.deselect_node(selectedId);
								tree.select_node(selectedId);
							}
                        }
                        if (json.error) alert(json.error);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            } else if (selectedId) {
                var node = tree.get_node(selectedId);
                $.ajax({
                    url: 'index.php?route=common/filemanager/move&token=<?php echo $token; ?>',
                    type: 'post',
                    data: 'from=' + encodeURIComponent(node.li_attr.directory) + '&to=' + encodeURIComponent(to),
                    dataType: 'json',
                    success: function(json) {
                        if (json.success) {
                            $('#dialog').remove();
                            tree.select_node('top');
                            tree.refresh_node('top');
                        }
                        if (json.error) alert(json.error);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            }
        });
    });

    // ----------------------------------------------------------
    // COPY
    // CHANGE: same pattern as move above.
    //         tree.parent(node) → tree.get_parent(nodeId)
    // ----------------------------------------------------------
    $('#copy').on('click', function() {
        $('#dialog').remove();

        var html = '<div id="dialog">'
                 + '<?php echo $entry_copy; ?> <input type="text" name="name" value="" /><br /><br />'
                 + '<input type="button" value="<?php echo $button_submit; ?>" />'
                 + '</div>';
        $('#column-right').prepend(html);

        $('#dialog').dialog({ title: '<?php echo $button_copy; ?>', resizable: false });

        $('#dialog input[type="button"]').on('click', function() {
            var tree = getTree();
            var selectedId = tree.get_selected()[0];
            var path = $('#column-right a.selected').find('input[name="image"]').attr('value');
            var name = $('#dialog input[name="name"]').val();

            if (path) {
                $.ajax({
                    url: 'index.php?route=common/filemanager/copy&token=<?php echo $token; ?>',
                    type: 'post',
                    data: 'path=' + encodeURIComponent(path) + '&name=' + encodeURIComponent(name),
                    dataType: 'json',
                    success: function(json) {
                        if (json.success) {
                            $('#dialog').remove();
							if (selectedId) {
								tree.deselect_node(selectedId);
								tree.select_node(selectedId);
							}
                        }
                        if (json.error) alert(json.error);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            } else if (selectedId) {
                var node = tree.get_node(selectedId);
                $.ajax({
                    url: 'index.php?route=common/filemanager/copy&token=<?php echo $token; ?>',
                    type: 'post',
                    data: 'path=' + encodeURIComponent(node.li_attr.directory) + '&name=' + encodeURIComponent(name),
                    dataType: 'json',
                    success: function(json) {
                        if (json.success) {
                            $('#dialog').remove();
                            // CHANGE: tree.parent(tree.selected) → tree.get_parent(selectedId)
                            var parentId = tree.get_parent(selectedId);
                            tree.select_node(parentId);
                            tree.refresh_node(parentId);
                        }
                        if (json.error) alert(json.error);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            }
        });
    });

    // ----------------------------------------------------------
    // RENAME — same pattern as copy/move
    // ----------------------------------------------------------
    $('#rename').on('click', function() {
        $('#dialog').remove();

        var html = '<div id="dialog">'
                 + '<?php echo $entry_rename; ?> <input type="text" name="name" value="" /><br /><br />'
                 + '<input type="button" value="<?php echo $button_submit; ?>" />'
                 + '</div>';
        $('#column-right').prepend(html);

        $('#dialog').dialog({ title: '<?php echo $button_rename; ?>', resizable: false });

        $('#dialog input[type="button"]').on('click', function() {
            var tree = getTree();
            var selectedId = tree.get_selected()[0];
            var path = $('#column-right a.selected').find('input[name="image"]').attr('value');
            var name = $('#dialog input[name="name"]').val();

            if (path) {
                $.ajax({
                    url: 'index.php?route=common/filemanager/rename&token=<?php echo $token; ?>',
                    type: 'post',
                    data: 'path=' + encodeURIComponent(path) + '&name=' + encodeURIComponent(name),
                    dataType: 'json',
                    success: function(json) {
                        if (json.success) {
                            $('#dialog').remove();
							if (selectedId) {
								tree.deselect_node(selectedId);
								tree.select_node(selectedId);
							}
                        }
                        if (json.error) alert(json.error);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            } else if (selectedId) {
                var node = tree.get_node(selectedId);
                $.ajax({
                    url: 'index.php?route=common/filemanager/rename&token=<?php echo $token; ?>',
                    type: 'post',
                    data: 'path=' + encodeURIComponent(node.li_attr.directory) + '&name=' + encodeURIComponent(name),
                    dataType: 'json',
                    success: function(json) {
                        if (json.success) {
                            $('#dialog').remove();
                            var parentId = tree.get_parent(selectedId);
                            tree.select_node(parentId);
                            tree.refresh_node(parentId);
                        }
                        if (json.error) alert(json.error);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            }
        });
    });

    // ----------------------------------------------------------
    // UPLOAD
    // CHANGE: $.tree.focused() references inside dialog callbacks
    //         replaced with getTree(). Logic otherwise unchanged.
    //         Note: plupload itself should work with jQuery 3.x fine.
    // ----------------------------------------------------------
    $('#upload').on('click', function() {
        var html = '<div id="upload-multi" title="<?php echo $text_upload_plus; ?>">'
                 + '<div id="uploader"></div>'
                 + '</div>';
        $('#column-left').prepend(html);

        $('#upload-multi').dialog({
            height: 360,
            width: <?php echo ($this->browser->checkMobile()) ? 600 : 730; ?>,
            modal: true,
            resizable: false,
            create: function() {
                $('#uploader').plupload({
                    runtimes: 'html5,flash,silverlight',
                    url: 'index.php?route=common/filemanager/multi&token=<?php echo $token; ?>&directory=' + window.dr,
                    max_file_count: 20,
                    max_file_size: '96mb',
                    chunk_size: '1mb',
                    unique_names: false,
                    resize: { quality: 100, crop: false },
                    filters: [{ title: "<?php echo $text_allowed; ?>", extensions: "jpg,jpeg,png,gif,mp3,mp4,oga,ogv,ogg,webm,m4a,m4v,wav,wma,wmv,zip,rar,pdf,flv,swf" }],
                    flash_swf_url: 'view/javascript/plupload/js/Moxie.swf',
                    silverlight_xap_url: 'view/javascript/plupload/js/Moxie.xap'
                });

                // CHANGE: $('form').submit() is fragile in a popup; kept as-is
                //         since plupload manages its own form internally.
                $('form').on('submit', function(e) {
                    var uploader = $('#uploader').plupload('getUploader');
                    if (uploader.files.length > 0) {
                        uploader.bind('StateChanged', function() {
                            if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                                $('form')[0].submit();
                            }
                        });
                        uploader.start();
                    } else {
                        alert('<?php echo $text_no_selection; ?>');
                        return false;
                    }
                });
            },
            close: function() {
                var tree = getTree();
                var selectedId = tree.get_selected()[0];
                // CHANGE: tree.select_branch(tree.selected) → select_node(selectedId)
				if (selectedId) {
					tree.deselect_node(selectedId);
					tree.select_node(selectedId);
				}
                $('#upload-multi').remove();
            }
        });
    });

    // ----------------------------------------------------------
    // REFRESH
    // CHANGE: tree.select_branch(tree.selected) → select_node(selectedId)
    //         Deselect then re-select forces the select_node event to fire
    //         and reload the file list.
    // ----------------------------------------------------------
    $('#refresh').on('click', function() {
        var tree = getTree();
        var selectedId = tree.get_selected()[0];
        if (selectedId) {
            tree.deselect_node(selectedId);
            tree.select_node(selectedId);
        }
    });

    // ----------------------------------------------------------
    // INFORMATION PANEL — logic unchanged, no API changes needed
    // ----------------------------------------------------------
    $('#information').on('click', function() {
        $.ajax({
            url: 'index.php?route=common/filemanager/information&token=<?php echo $token; ?>',
            dataType: 'json',
            type: 'get',
            success: function(json) {
                $('.success, .warning, .attention, .error').remove();
                if (json['html']) {
                    $('#information-dialog').html(json['html']);
                    $('#information-dialog').dialog({
                        title: '<?php echo $heading_info; ?>',
                        width: 620,
                        height: 265,
                        resizable: false,
                        modal: true
                    });
                } else {
                    alert('Invalid response!');
                }
            },
            error: function() {
                alert('Ajax error!');
            }
        });
    });

});
//--></script>
</body>
</html>