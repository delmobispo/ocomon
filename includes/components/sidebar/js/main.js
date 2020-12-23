jQuery(function ($) {

  if ($('#s_logado').val() == 1) {

    loadPage("menu-sidebar.php #sidebar-loaded", loadMenuObserver());

    $('a.barra').on('click', function(){
      var menu = $(this).prop('id');
      $(this).addClass("barra-selected");

      if (menu != 'HOME'){
        $('#HOME').removeClass('barra-selected');
      }
      if (menu != 'OCOMON'){
        $('#OCOMON').removeClass('barra-selected');
      }
      if (menu != 'INVMON'){
        $('#INVMON').removeClass('barra-selected');
      }
      if (menu != 'ADMIN'){
        $('#ADMIN').removeClass('barra-selected');
      }
    });
  
    
    $(function () {
      $('[data-toggle="popover"]').popover();
    });

    $(".popover-dismiss").popover({
      trigger: "focus",
    });
    
  
  } else {
    $('#idLogin').focus();
  }
  
});

function loadPage(app) {
  $("#sidebar").load(app);

  $('#HOME').addClass('barra-selected');
  loadPageContent('./ocomon/geral/tickets_main_user.php');

  // loadMenuObserver();
}


function loadPageContent(src) {

  var sessionPageHome = $('#defaultPageHome').val();
  var sessionPageOcomon = $('#defaultPageOcomon').val();
  var sessionPageInvmon = $('#defaultPageInvmon').val();
  var sessionPageAdmin = $('#defaultPageAdmin').val();

    if (src == 'hom')
    $("#iframeMain").attr("src", sessionPageHome);
    else if (src == 'admin')
      $("#iframeMain").attr("src", sessionPageAdmin);
    else if (src == 'oco')
      $("#iframeMain").attr("src", sessionPageOcomon);
    else if (src == 'inv')
      $("#iframeMain").attr("src", sessionPageInvmon);
    else
    $("#iframeMain").attr("src", src);
}

function loadMenuObserver() {

  /* jQuery.initialize plugin is created to help maintain dynamically created elements on the
  page.  */
  var obs = $.initialize("#sidebar-loaded", function () {
    // Dropdown menu
    $(".sidebar-dropdown > a").click(function () {
      $(".sidebar-submenu").slideUp(200);
      if ($(this).parent().hasClass("active")) {
        $(".sidebar-dropdown").removeClass("active");
        $(this).parent().removeClass("active");
      } else {
        $(".sidebar-dropdown").removeClass("active");
        $(this).next(".sidebar-submenu").slideDown(200);
        $(this).parent().addClass("active");
      }

      // var href = $(this).attr('href');
      // alert (href);
      // obs.disconnect();
    });

    $(".sidebar-submenu > ul > li > a, .li-link > a").click(function (e) {
      e.preventDefault();
      var path = $(this).attr("data-path");
      var app = $(this).attr("data-app");
      var params = $(this).attr("data-params");

      var queryString = "";

      if (params != '') {
        queryString = "?" + params;
      }



      // alert($(this).attr("href"));
      // alert(path+app+'php');

      // $("#centro2").attr("src", $(this).attr("href"));
      $("#iframeMain").attr("src", path + app + ".php" + queryString);



      // obs.disconnect();
      // alert (path+app+'.php');
      // loadPage(path+app+'.php');
    });

    //toggle sidebar
    $(".toggle-sidebar").click(function () {
      $(".page-wrapper").toggleClass("toggled");
    });

    // bind hover if pinned is initially enabled
    if ($(".page-wrapper").hasClass("pinned")) {
      $("#sidebar").hover(
        function () {
          // console.log("mouseenter");
          $(".page-wrapper").addClass("sidebar-hovered");
        },
        function () {
          // console.log("mouseout");
          $(".page-wrapper").removeClass("sidebar-hovered");
        }
      );
    }

    //Pin sidebar
    $(".pin-sidebar").click(function () {
      if ($(".page-wrapper").hasClass("pinned")) {
        // unpin sidebar when hovered
        $(".page-wrapper").removeClass("pinned");
        $("#sidebar").unbind("hover");
      } else {
        $(".page-wrapper").addClass("pinned");
        $("#sidebar").hover(
          function () {
            // console.log("mouseenter");
            $(".page-wrapper").addClass("sidebar-hovered");
          },
          function () {
            // console.log("mouseout");
            $(".page-wrapper").removeClass("sidebar-hovered");
          }
        );
      }
    });

    //toggle sidebar overlay
    $("#overlay").click(function () {
      $(".page-wrapper").toggleClass("toggled");
    });

    //switch between themes
    var themes =
      "default-theme legacy-theme chiller-theme ice-theme cool-theme light-theme";
    $("[data-theme]").click(function () {
      $("[data-theme]").removeClass("selected");
      $(this).addClass("selected");
      $(".page-wrapper").removeClass(themes);
      $(".page-wrapper").addClass($(this).attr("data-theme"));
    });

    // switch between background images
    var bgs = "bg1 bg2 bg3 bg4";
    $("[data-bg]").click(function () {
      $("[data-bg]").removeClass("selected");
      $(this).addClass("selected");
      $(".page-wrapper").removeClass(bgs);
      $(".page-wrapper").addClass($(this).attr("data-bg"));
    });

    // toggle background image
    $("#toggle-bg").change(function (e) {
      e.preventDefault();
      $(".page-wrapper").toggleClass("sidebar-bg");
    });

    // toggle border radius
    $("#toggle-border-radius").change(function (e) {
      e.preventDefault();
      $(".page-wrapper").toggleClass("border-radius-on");
    });

    //custom scroll bar is only used on desktop
    if (
      !/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
      )
    ) {
      $(".sidebar-content").mCustomScrollbar({
        axis: "y",
        autoHideScrollbar: true,
        scrollInertia: 300,
      });
      $(".sidebar-content").addClass("desktop");
    }


    $(function () {
      $('[data-toggle="popover"]').popover();
    });

    $(".popover-dismiss").popover({
      trigger: "focus",
    });


  }, { target: document.getElementById('sidebar') });


}

function loadMenu() {


  
    // Dropdown menu
    $(".sidebar-dropdown > a").click(function () {
      $(".sidebar-submenu").slideUp(200);
      if ($(this).parent().hasClass("active")) {
        $(".sidebar-dropdown").removeClass("active");
        $(this).parent().removeClass("active");
      } else {
        $(".sidebar-dropdown").removeClass("active");
        $(this).next(".sidebar-submenu").slideDown(200);
        $(this).parent().addClass("active");
      }

      // var href = $(this).attr('href');
      // alert (href);
    });

    $(".sidebar-submenu > ul > li > a, .li-link > a").click(function (e) {
      e.preventDefault();
      var path = $(this).attr("data-path");
      var app = $(this).attr("data-app");
      var params = $(this).attr("data-params");

      var queryString = "";

      if (params != '') {
        queryString = "?" + params;
      }

      // $("#centro2").attr("src", $(this).attr("href"));
      $("#iframeMain").attr("src", path + app + ".php" + queryString);

      // alert (path+app+'.php');
      // loadPage(path+app+'.php');
    });

    //toggle sidebar
    $(".toggle-sidebar").click(function () {
      $(".page-wrapper").toggleClass("toggled");
    });

    // bind hover if pinned is initially enabled
    if ($(".page-wrapper").hasClass("pinned")) {
      $("#sidebar").hover(
        function () {
          // console.log("mouseenter");
          $(".page-wrapper").addClass("sidebar-hovered");
        },
        function () {
          // console.log("mouseout");
          $(".page-wrapper").removeClass("sidebar-hovered");
        }
      );
    }

    //Pin sidebar
    $(".pin-sidebar").click(function () {
      if ($(".page-wrapper").hasClass("pinned")) {
        // unpin sidebar when hovered
        $(".page-wrapper").removeClass("pinned");
        $("#sidebar").unbind("hover");
      } else {
        $(".page-wrapper").addClass("pinned");
        $("#sidebar").hover(
          function () {
            // console.log("mouseenter");
            $(".page-wrapper").addClass("sidebar-hovered");
          },
          function () {
            // console.log("mouseout");
            $(".page-wrapper").removeClass("sidebar-hovered");
          }
        );
      }
    });

    //toggle sidebar overlay
    $("#overlay").click(function () {
      $(".page-wrapper").toggleClass("toggled");
    });

    //switch between themes
    var themes =
      "default-theme legacy-theme chiller-theme ice-theme cool-theme light-theme";
    $("[data-theme]").click(function () {
      $("[data-theme]").removeClass("selected");
      $(this).addClass("selected");
      $(".page-wrapper").removeClass(themes);
      $(".page-wrapper").addClass($(this).attr("data-theme"));
    });

    // switch between background images
    var bgs = "bg1 bg2 bg3 bg4";
    $("[data-bg]").click(function () {
      $("[data-bg]").removeClass("selected");
      $(this).addClass("selected");
      $(".page-wrapper").removeClass(bgs);
      $(".page-wrapper").addClass($(this).attr("data-bg"));
    });

    // toggle background image
    $("#toggle-bg").change(function (e) {
      e.preventDefault();
      $(".page-wrapper").toggleClass("sidebar-bg");
    });

    // toggle border radius
    $("#toggle-border-radius").change(function (e) {
      e.preventDefault();
      $(".page-wrapper").toggleClass("border-radius-on");
    });

    //custom scroll bar is only used on desktop
    if (
      !/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
      )
    ) {
      $(".sidebar-content").mCustomScrollbar({
        axis: "y",
        autoHideScrollbar: true,
        scrollInertia: 300,
      });
      $(".sidebar-content").addClass("desktop");
    }

}
