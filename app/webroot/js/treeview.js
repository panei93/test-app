(function($) {
      $.extend($.fn, {
         swapClass: function(c1, c2) {
            var c1Elements = this.filter('.' + c1);
            this.filter('.' + c2).removeClass(c2).addClass(c1);
            c1Elements.removeClass(c1).addClass(c2);
            return this;
         },
         prepareParents: function() {
            var elements = $(this).find(".level");
            elements.each(function(index) {
               if ($(this).find(".item").length > 0 && !$(this).parent().has("i").length) {
                  $(this).parent().find(">span").prepend("<i class='glyphicon glyphicon-plus'></i> ");
               }

               if ($(this).hasClass("closed")) {
                  $(this).hide();
               } else {
                  $(this).parent().find(">span").find(">i").swapClass(CLASSES.minus, CLASSES.plus);
               }
            });
         },
         handleClick: function(e, callback) {
            var children = $(this).parent().find(".level");
            if(children.context.id != 'firstNode') {/* not root node*/
               var el = document.getElementById(children.context.id).nextSibling;
               var childLen = el.nextSibling.children.length;
            }
            
            if (children.is(":visible")) {
               var element = null;
               if (children.length > 1)
                  element = $(children[0]);
               else
                  element = children;
               element.hide('fast');
               $(this).find(">i").swapClass(CLASSES.plus, CLASSES.minus);
               if(children.context.id == 'firstNode') 
                  $("#firstNode").css({'margin-top':'184px'});
            } else {
               $("#firstNode").css({'margin-top':'-15px'});
               element = null;
               if (children.length > 1) 
                  element = $(children[0]);
               else
                  element = children;

               if(childLen > 0 || children.context.id == 'firstNode') element.show('fast');
               
               $(this).find(">i").swapClass(CLASSES.plus, CLASSES.minus);
               if (callback) callback(this);
                  e.stopPropagation();
            }
         },
         treeview: function(callback) {
            var elements = $(this).find(".item > span");
            elements.unbind("click");
            elements.on('click', function(e) {
               $(this).handleClick(e, callback);
            });
            var rootElement = $(this).parent().find(".root > span");
            rootElement.unbind("click");
            rootElement.on('click', function(e) {
               if($("#firstNode i").attr('class').indexOf('minus') != -1) {
                  $(".tree-container").removeClass('overflow');
               }else {
                  $(".tree-container").addClass('overflow');
               }
               $(this).handleClick(e, callback);
            });
            $(this).prepareParents();
         }
      });
      $.treeview = {};
      var CLASSES = ($.treeview.classes = {
         open: "open",
         closed: "closed",
         plus: "glyphicon-plus",
         minus: "glyphicon-minus"
      });
   })(jQuery);