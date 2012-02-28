/*
The MIT License

Copyright (c) 2008 David Kwast <david _dot_ kwast _at_ gmail>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

//keeps closure
(function ($) {
    
    var binded = false,
    verifySizeList = [],
    
	//jakemod: this variable used to be declared inside $.fn.jqcanvas->this.each; it is now remembered outside of the plugin.
	jqcanvas_id = $('canvas').size();
	
    $.fn.jqcanvas = function (callback, options){

       this.each(function () {
            var opts = $.extend({}, $.fn.jqcanvas.defaults, options),
            
            $this = $(this),
			top = $this.offset().top,
			left = $this.offset().left,
			width = getMasterWidth($this),
			height = getMasterHeight($this);
            
			/* 
			 jakemod: this variable used to assign ID based purely on number of canvas elements existing in the DOM, 
			 but caused a bug when dynamically removing and inserting new canvas elements; Duplicate IDs were being generated,
			 and un-targeted canvases would disappear since they shared an ID.
            		*/
			
			//var jqcanvas_id = $('canvas').size();
			
			//jakemod: Simple increment ensures unique ID for every canvas element
			jqcanvas_id += 1;
			
            var canvas_id = "jqcanvas_"+jqcanvas_id;
            
            $this.data("callback", callback);
            $this.data("top", top);
            $this.data("left", left);
            $this.data("width", width);
            $this.data("extraWidth", opts.extraWidth);
            $this.data("height", height);
            $this.data("jqcanvas_id", canvas_id);

            
            var canvas = $(create_canvas(canvas_id, top, left, width, height, $this, name, options));
            
            canvas.each(function () {
                redraw($this, this, options);//this == canvas element (not jquery obj)
            });
            
            if ( opts.verifySize ){
                verifySizeList.push($this);
                if ( !(binded) ){//Only bind this events once
                    binded = true;
                    setInterval(verifyResize, $.fn.jqcanvas.global_options.verifyResizeInterval);
                    $(window).resize(verifyResize);
                }
            }
        });
	   return $(this);
    };
    
    $.fn.jqcanvas.defaults = {
        extraWidth: 0,
        verifySize: true
    }
    $.fn.jqcanvas.global_options = {
        verifyResizeInterval: 1000
    }
    
    function hasMoved(master){
        var offset = master.offset();
        if (offset.top != master.data("top")){return true}
        else if (offset.left != master.data("left")){return true}
        else {return false};
    }
    function create_canvas(canvas_id, top, left, width, height, $this, name, options){

        var border_left = parseInt($this.css('borderLeftWidth')) || 0;
        var border_top = parseInt($this.css('borderTopWidth')) || 0;
        var position_left = parseInt(left);
        var position_top = parseInt(top);
        
        var new_position_left = (position_left+border_left).toString() + 'px'
        var new_position_top = (position_top+border_top).toString() + 'px'
        
        var canvasCSS = {
            zIndex: options.z || 1,
            position: "static"//,
            //left: new_position_left,
           // top: new_position_top
        };


        
        var new_width = width + $this.data("extraWidth");
        
        // Maybe we can refactor here
        var el = document.createElement('canvas');
        el.setAttribute('id', canvas_id);
        el.setAttribute('width', new_width);
        el.setAttribute('height', height);
        if($.browser.msie && $.browser.version < 9) {
            G_vmlCanvasManager.initElement(el);
        };
        var canvas = $(el);
		
        $this.prepend(canvas);
		canvas.data("classes", options.className);
		
        canvas.addClass("jqcanvas " +  options.className );
//         canvas.width(width);
//         canvas.height(height);
        canvas.css(canvasCSS);
        return el;
    }
    function redraw(master, canvas, options){
        var width = getMasterWidth(master);
        var height = getMasterHeight(master);
        
        // Remove previous canvas and create it again with new width and height
        var canvas_id = master.data("jqcanvas_id");
		var name = $('#'+canvas_id).data('classes');
        $('#'+canvas_id).remove();
        
        canvas = create_canvas(canvas_id, master.data("top"), master.data("left"), width, height, master, name, options);
        
        master.data("jqcanvas",$(canvas));
        
        // Canvas size can be different from its master element size
        master.data("callback")(canvas, $(canvas).width(), $(canvas).height());
    }
    function move(master, canvas, top, left){
        $(canvas).css({
            top: top,
            left: left
        });
//         if($.browser.msie){
//            redraw(master, canvas);
//         }
    }
    function verifyMove(master, canvas){
        if (hasMoved(master)){
            var offset = master.offset();
            canvas.each(function(){
                move(master, this, offset.top, offset.left);//this == canvas element (not jquery obj)
            });
            master.data("top",offset.top);
            master.data("left",offset.left);
        }
    }
    function getMasterWidth(master){
        return parseInt(master.css("padding-right")) + parseInt(master.css("padding-left")) + master.width()
    }
    function getMasterHeight(master){
        return parseInt(master.css("padding-bottom")) + parseInt(master.css("padding-top")) + master.height()
    }
    function hasResized(master){
        var width = getMasterWidth(master)
        var height = getMasterHeight(master)
        if (width != master.data("width")){return true}
        else if (height != master.data("height")){return true}
        else {return false};
    }
    function verifyResize(){
        jQuery.map(verifySizeList, function(master){
            var canvas = master.data("jqcanvas");
            verifyMove(master, canvas);
            if (hasResized(master)){
                canvas.each(function(){
                    redraw(master, this);//this == canvas element (not jquery obj)
                });
                master.data("width",getMasterWidth(master));
                master.data("height",getMasterHeight(master));
            }
        });
    }
})(jQuery);