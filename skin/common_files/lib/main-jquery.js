$(function() {
	/*窗口变化时运行*/
	$(window).resize(function() {
		/*全屏幕高度*/
		$(".parallax").css("min-height",$(window).height());
	});
	/*滚动条滚动时运行*/
	$(window).scroll(function(){
		
	});
	/*全屏幕高度*/
	$(".parallax").css("min-height",$(window).height());
	
	var mySwiper = new Swiper('.home-banner .swiper-container', {
		autoplay:3000,
		speed: 1000,
		pagination: '.home-banner .swiper-pagination',
        paginationClickable: true
		
	});
	
	var mySwiper = new Swiper('#menu-details-1 .swiper-container', {
		speed: 1000,
		slidesPerView: 3,
		spaceBetween: 25,
		nextButton: '#menu-details-1 .swiper-button-next',
        prevButton: '#menu-details-1 .swiper-button-prev',
		paginationClickable: true,
		/*breakpoints: {
		// when window width is <= 320px
		320: {
		  slidesPerView: 1,
		  spaceBetweenSlides: 10
		},
		// when window width is <= 480px
		480: {
		  slidesPerView: 2,
		  spaceBetweenSlides: 20
		},
		// when window width is <= 640px
		640: {
		  slidesPerView: 3,
		  spaceBetweenSlides: 30
		},
		768: {
		  slidesPerView: 4,
		  spaceBetweenSlides: 30
		}
		}*/
	});
	
	if($(".menu-details").length > 0){
		$(".menu-details .col-md-1").height($(".menu-details-photo .ac").height());
		//alert($(".menu-details-photo .ac").height());
	}
	
	if($("#sortby").length > 0){
		$("#sortby").change(function(e) {
      var url = $(this).val();
			location.assign(current_location + '/' + url);
    });
	}
		$("#searchby").change(function(e) {
      var searchby = $(this).val();
			$(".searchby").val('')
			$("#"+searchby).val('Y');
    });
	
	$(".p-detail-pop-link").click(function(){
		$("#p-detail-pop").fadeIn();
		return false;
	});
	$(".close-pop").click(function(){
		$(this).parents(".pop-up-warp").fadeOut();
		return false;
	});
	
	if($("#inquiry-form").length > 0){
		$("#inquiry-form").submit(function(e) {
			$(".btn-primary",this).prop("disabled",true);
      var $product = $("#product").val(), $productid = $("#productid").val(), $name = $("#name").val(), $company = $("#company").val(), $email = $("#email").val(), $phone = $("#phone").val(), $comments = $("#comments").val();
			var data = {product:$product,productid:$productid,name:$name,company:$company,email:$email,phone:$phone,comments:$comments};
			$.ajax({
				type: "POST",
				url: "inquiry.php",
				data: data,
				cache:false,
				error: function() {
					alert("System Error !!");
				},
				success: function(cmsg){
					if(cmsg){
						console.log(cmsg);
						$("#form-main").hide();
						$("#response-main").show();
					}
				}
			});
			return false;
    });
	}
});
/*old menu*/
/*$(document).ready(function(){
	var menu_head = $(".menu-head");
    menu_head.hover(function(index) {
		$(this).parent().find(".menu-body").slideDown('fast').show();
        $(this).parent().hover(
			function() {
				$(this).find(".menu-head").addClass("aa1");
            }, function(){
            	$(this).parent().find(".menu-body").slideUp('fast').end().find(".menu-head").removeClass("aa1");
        });
    });
});*/
/*点击滚动到#id, click_scroll()运行*/
function click_scroll(){
	if($("a[href^='#']").length > 0){
		$("a").click(function(){
			if($(this).attr("href").length >= 2){
				$("body").animate({scrollTop: $($(this).attr("href")).offset().top}, 1000);
				return false;
			}
		});
	}
}
/*parallax,$(".dome").parallax()*/
(function( $ ){
	var $window = $(window);
	var windowHeight = $window.height();
	$window.resize(function () {
		windowHeight = $window.height();
	});

	$.fn.parallax = function(xpos, speedFactor, outerHeight) {
		var $this = $(this);
		var getHeight;
		var firstTop;
		var paddingTop = 0;
		//get the starting position of each element to have parallax applied to it	
		function update (){
			
			$this.each(function(){
								
				firstTop = $this.offset().top;
			});
	
			if (outerHeight) {
				getHeight = function(jqo) {
					return jqo.outerHeight(true);
				};
			} else {
				getHeight = function(jqo) {
					return jqo.height();
				};
			}
				
			// setup defaults if arguments aren't specified
			if (arguments.length < 1 || xpos === null) xpos = "50%";
			if (arguments.length < 2 || speedFactor === null) speedFactor = 1.2;
			if (arguments.length < 3 || outerHeight === null) outerHeight = true;
			
			// function to be called whenever the window is scrolled or resized
			
				var pos = $window.scrollTop();				
	
				$this.each(function(){
					var $element = $(this);
					var top = $element.offset().top;
					var height = getHeight($element);
	
					// Check if totally above or totally below viewport
					if (top + height < pos || top > pos + windowHeight) {
						return;
					}
					
					$this.css('backgroundPosition', xpos + " " + Math.round((firstTop - pos) * speedFactor) + "px");
					
				});
		}		

		$window.bind('scroll', update).resize(update);
		update();
	};
})(jQuery);
/*scrolltotop*/
var scrolltotop={
	setting: {startline:100, scrollto: 0, scrollduration:500, fadeduration:[500, 100]},
	controlHTML: '<button class="footer-btn" style="position: relative;"><span class="glyphicon glyphicon-menu-up" style="color:#fff;"></span></button>', 
	controlattrs: {offsetx:5, offsety:5}, 
	anchorkeyword: '#top', 
	state: {isvisible:false, shouldvisible:false},
	scrollup:function(){
		if (!this.cssfixedsupport){this.$control.css({opacity:0}); };
		var dest=isNaN(this.setting.scrollto)? this.setting.scrollto : parseInt(this.setting.scrollto);
		if (typeof dest=="string" && jQuery('#'+dest).length==1){dest=jQuery('#'+dest).offset().top;}
		else{dest=0;};
		this.$body.animate({scrollTop: dest}, this.setting.scrollduration);
	},

	keepfixed:function(){
		var $window=jQuery(window);
		var controlx=$window.scrollLeft() + $window.width() - this.$control.width() - this.controlattrs.offsetx;
		var controly=$window.scrollTop() + $window.height() - this.$control.height() - this.controlattrs.offsety;
		this.$control.css({left:controlx+'px', top:controly+'px'});
	},

	togglecontrol:function(){
		var scrolltop=jQuery(window).scrollTop();
		if (!this.cssfixedsupport){this.keepfixed();};
		this.state.shouldvisible=(scrolltop>=this.setting.startline)? true : false;
		if (this.state.shouldvisible && !this.state.isvisible){
			this.$control.stop().animate({opacity:1}, this.setting.fadeduration[0]);
			this.state.isvisible=true;
		}
		else if (this.state.shouldvisible==false && this.state.isvisible){
			this.$control.stop().animate({opacity:0}, this.setting.fadeduration[1]);
			this.state.isvisible=false;
		}
	},
	
	init:function(){
		jQuery(document).ready(function($){
			var mainobj=scrolltotop;
			var iebrws=document.all;
			mainobj.cssfixedsupport=!iebrws || iebrws && document.compatMode=="CSS1Compat" && window.XMLHttpRequest;
			mainobj.$body=(window.opera)? (document.compatMode=="CSS1Compat"? $('html') : $('body')) : $('html,body');
			mainobj.$control=$('<div id="topcontrol">'+mainobj.controlHTML+'</div>')
				.css({position:mainobj.cssfixedsupport? 'fixed' : 'absolute', bottom:mainobj.controlattrs.offsety, right:mainobj.controlattrs.offsetx, opacity:0, cursor:'pointer'})
				.attr({title:'Scroll Back to Top'})
				.click(function(){mainobj.scrollup(); return false})
				.appendTo('body');
			if (document.all && !window.XMLHttpRequest && mainobj.$control.text()!=''){mainobj.$control.css({width:mainobj.$control.width()}); }
			mainobj.togglecontrol();
			$('a[href="' + mainobj.anchorkeyword +'"]').click(function(){
				mainobj.scrollup();
				return false;
			});
			$(window).bind('scroll resize', function(e){
				mainobj.togglecontrol();
			})
		})
	}
};

scrolltotop.init();
