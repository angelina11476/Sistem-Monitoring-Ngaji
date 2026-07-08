// Minimal JS for modal handling and small UI helpers
(function(){
    window.openModal = function(id){
        var el = document.getElementById(id);
        if(!el) return;
        el.setAttribute('aria-hidden','false');
    }
    window.closeModal = function(id){
        var el = document.getElementById(id);
        if(!el) return;
        el.setAttribute('aria-hidden','true');
    }

    // Auto-bind buttons with data-open-modal and data-close-modal
    document.addEventListener('click', function(e){
        var t = e.target;
        if(!t) return;
        var open = t.getAttribute && t.getAttribute('data-open-modal');
        var close = t.getAttribute && t.getAttribute('data-close-modal');
        if(open){ e.preventDefault(); openModal(open); }
        if(close){ e.preventDefault(); closeModal(close); }
    });
})();
