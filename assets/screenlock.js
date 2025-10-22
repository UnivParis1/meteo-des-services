import modal_mobile from "./snippets/modal_mobile.html";
require('jquery-stage');

var modal_global_mobile = modal_mobile;

  $(document).ready(function () {
    let isMobile = /mobile/i.test(navigator.userAgent);

    if (isMobile) {
      $('body').after(modal_global_mobile);

      if ($.stage().orientation == 'portrait') {
        $('#staticBackdrop').modal('show');
      }

      $('#staticBackdrop .modal-footer button.btn-primary').on('click', function () {

        $('#staticBackdrop').modal('hide');

        lock('landscape')
          .then( () => console.log("lock landscape") )
          .catch( (err) => console.log(err) );
      });
    }

  });

  // (A) LOCK SCREEN ORIENTATION
  async function lock(orientation) {
    // (A1) GO INTO FULL SCREEN FIRST
    let de = document.documentElement;
    if (de.requestFullscreen) { de.requestFullscreen(); }
    else if (de.mozRequestFullScreen) { de.mozRequestFullScreen(); }
    else if (de.webkitRequestFullscreen) { de.webkitRequestFullscreen(); }
    else if (de.msRequestFullscreen) { de.msRequestFullscreen(); }

    screen.orientation.unlock();
    // (A2) THEN LOCK ORIENTATION
    return screen.orientation.lock(orientation)
      .then(() => console.log('lock réussit pour ' + orientation))
      .catch((error) => {
        console.log("erreur pour le lock ")
        throw error;
      });
  }

  // (B) UNLOCK SCREEN ORIENTATION
  function unlock() {
    // (B1) UNLOCK FIRST
    screen.orientation.unlock();

    // (B2) THEN EXIT FULL SCREEN
    if (document.exitFullscreen) { document.exitFullscreen(); }
    else if (document.webkitExitFullscreen) { document.webkitExitFullscreen(); }
    else if (document.mozCancelFullScreen) { document.mozCancelFullScreen(); }
    else if (document.msExitFullscreen) { document.msExitFullscreen(); }
  }
