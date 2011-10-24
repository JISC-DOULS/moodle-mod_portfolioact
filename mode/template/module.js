M.portfolioactmode_template = M.portfolioactmode_template || {};

/*
function customhook_for_control_of_editor(options) {
    return options;
}
*/


//code for edittemplate.php
M.portfolioactmode_template.init = function(Y) {
    M.portfolioactmode_template.Y = Y;

    //show/hide the pages select box
   showpages = function(event) {
        var idx = this.get('selectedIndex');
        var choice = this.get("options").item(idx).getAttribute('value');
       //setAttribute('disabled','disabled');

        if (choice == 1) {
            M.portfolioactmode_template.Y.one('#id_pages').setStyle("display", "block");
        }    else {
            M.portfolioactmode_template.Y.one('#id_pages').setStyle("display", "none");
        }

}

    //load the pages for the current template
    M.portfolioactmode_template.refresh_pages = function(event) {

        var newtemplateid  = M.portfolioactmode_template.Y.get('#id_template').get('value');
        var cmid = M.portfolioactmode_template.Y.get('#cmid').get('value');
        var url = M.cfg.wwwroot+'/mod/portfolioact/mode/template/edittemplate.php?id='+cmid+'&newtemplateid='+newtemplateid;
        document.location.href = url;

    };

    Y.on('change',M.portfolioactmode_template.refresh_pages,'#id_template');
    Y.on('change',showpages,'#id_pagespecific');

};




//code for designer.php
M.portfolioactmode_template.init_page = function(Y,templateid,cmid) {

    var highestPosition = 1;
    Y.all('.portfolioact-template-pos').each(
        function(node,idx) {
           var pos = node.get('text');
           if (pos > highestPosition) {
              highestPosition = pos;
           }
        }

    );

    Y.on('click',updatepos,'span.portfolioact-template-sort-arrow > img');

    //gets the item order sends to the server to update
    function updatepos(e) {

        //get the current position of the page being changed
        var arrow = e.target.get('parentNode');
        var target_id = arrow.getAttribute('id');
        var dataId = target_id.replace(/up|down/,'position');
        var currentPosition = Y.one('#'+dataId).get('text');

        //get the id of the page being changed
        var patt1 = /page_(?:up|down)_(\d+)$/;
        var matches2 = target_id.match(patt1);
        var beingChangedPageId = matches2[1];

        //build hash of positions before change
        var patt2 = /^page_position_(\d+)$/;
        currentPos = {};
        Y.all('.portfolioact-template-pos').each(
                function(node,idx) {
                   var pos = node.get('text');
                    var id = node.get('id');
                    var matches = id.match(patt2);
                    var pageid = matches[1];
                    currentPos[pageid] = pos;
                 }
          );

        //set the new position
        if (arrow.hasClass('portfolioact-template-sort-arrow-up')) {
            if ( currentPosition*1 > 1 )   {
                var newPos = currentPosition*1-1;
            }
            else {
                return;    //this should not happen
            }

        } else {

            if (currentPosition*1 < highestPosition ) {
                var newPos = currentPosition*1+1;

            } else {
                return; //this should not happen
            }
        }

        //modify the hash - make the one the being changed one is replacing get the old position (this swaps them)
        Y.Object.each(currentPos,
              function(pos, id) {
                if (pos == newPos ) {
                   currentPos[id] =  currentPosition;
                  return true;
                }
              }

          );
        //modify the hash - set the new position
        currentPos[beingChangedPageId] = newPos;

        //construct pageorderlist
        var pageorderlist = "";
        Y.Object.each(currentPos,
                function(pos, id) {
                    pageorderlist = pageorderlist + id + '|' + pos + '#';
                 }

         );

        //set the field
        Y.one('#pageorderlist').set('value',pageorderlist);
        Y.one('.portfolioact-template-posform').submit();
    }

};






//code for pageeditor.php
M.portfolioactmode_template.init_items = function(Y,pageid,cmid) {

    var highestPosition = 1;
    Y.all('.portfolioact-template-pos').each(
        function(node,idx) {
           var pos = node.get('text');
           if (pos > highestPosition) {
              highestPosition = pos;
           }
        }

    );

    Y.on('click',updatepos,'span.portfolioact-template-sort-arrow > img');

    //gets the item order sends to the server to update
    function updatepos(e) {

        //get the current position of the item being changed
        var arrow = e.target.get('parentNode');
        var target_id = arrow.getAttribute('id');
        var dataId = target_id.replace(/up|down/,'position');
        var currentPosition = Y.one('#'+dataId).get('text');

        //get the id of the item being changed
        var patt1 = /item_(?:up|down)_(\d+)$/;
        var matches2 = target_id.match(patt1);
        var beingChangedItemId = matches2[1];

        //build hash of positions before change
        var patt2 = /^item_position_(\d+)$/;
        currentPos = {};
        Y.all('.portfolioact-template-pos').each(
                function(node,idx) {
                   var pos = node.get('text');
                    var id = node.get('id');
                    var matches = id.match(patt2);
                    var itemid = matches[1];
                    currentPos[itemid] = pos;
                 }
          );

        //set the new position
        if (arrow.hasClass('portfolioact-template-sort-arrow-up')) {
            if ( currentPosition*1 > 1 )   {
                var newPos = currentPosition*1-1;
            }
            else {
                return;    //this should not happen
            }

        } else {

            if (currentPosition*1 < highestPosition ) {
                var newPos = currentPosition*1+1;

            } else {
                return; //this should not happen
            }
        }

        //modify the hash - make the one the being changed one is replacing get the old position (this swaps them)
        Y.Object.each(currentPos,
              function(pos, id) {
                if (pos == newPos ) {
                   currentPos[id] =  currentPosition;
                  return true;
                }
              }

          );
        //modify the hash - set the new position
        currentPos[beingChangedItemId] = newPos;

        //construct pageorderlist
        var itemorderlist = "";
        Y.Object.each(currentPos,
                function(pos, id) {
                    itemorderlist = itemorderlist + id + '|' + pos + '#';
                 }

         );

        //set the field
        Y.one('#itemorderlist').set('value',itemorderlist);
        Y.one('.portfolioact-template-posform').submit();
    }

};
