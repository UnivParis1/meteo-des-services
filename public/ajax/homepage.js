// POP UP DETAIL

function showDetails(applicationId) {
    // Affecte les éléments de la pop
    $.ajax({
        url: '/meteo/application/' + applicationId, // renvoie le contenu de la pop-up
        method: 'GET',
        success: function(response) {
            $('#details-content').html(response);
            disableHomepageActions('main-block');
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}

function confirmDeletion(event, url, entityName) {
    event.preventDefault(); // Empêche la redirection immédiate

    console.log("work");
    // Affiche la popup de confirmation
    if (confirm("Êtes-vous sûr de vouloir supprimer cette " + entityName + " ?")) {
        window.location.href = url; // Redirige l'utilisateur si l'action est confirmée
    }
}

function hideContent(contentId) {
    document.getElementById(contentId).style.display = "none";
    enableHomepageActions("main-block");
}

// ACTIONS SUR LA PAGE D'ACCUEIL
function disableHomepageActions(blockId)
{
    document.getElementById(blockId).style.pointerEvents = "none";
    document.getElementById(blockId).style.opacity = "0.25";
}

function enableHomepageActions(blockId)
{
    document.getElementById(blockId).style.pointerEvents = "auto";
    document.getElementById(blockId).style.opacity = "1";
}
