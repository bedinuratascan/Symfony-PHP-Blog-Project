$(document).ready(function () {
   removeHamburgerMenuFromUserPage();
});

function removeHamburgerMenuFromUserPage() {
    let pathname = document.location.pathname;

    if ( pathname !== '/user/' )
        return;

    let icon = document.querySelector('div#toggler');
    icon.remove();

    let classyMenu = document.querySelector('div.classy-menu');
    classyMenu.style = "width: 100%;";

    let classyNav = document.querySelector('div.classynav');
    classyNav.style = 'position: relative; width: 100%;';

    let menu = document.querySelector('div.classynav > ul');
    menu.style = 'margin: 0 auto;';

    let loginArea = document.querySelector('div.login-area');
    loginArea.style = 'position: absolute; right: 0;'
}
