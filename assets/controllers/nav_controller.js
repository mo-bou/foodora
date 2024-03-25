import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['navlink']
    connect() {
        // const navLinks = Array.from(this.element.getElementsByClassName('nav-link'));
        // navLinks.forEach((navLink) => {
        //     navLink.addEventListener('click', (event) => {
        //         event.preventDefault();
        //
        //         navLinks.forEach((link) => {
        //             link.attributes.removeNamedItem('aria-current');
        //             link.classList.remove('active');
        //         })
        //         console.log(navLink);
        //         navLink.classList.add('active');
        //         navLink.setAttribute('aria-current', 'page');
        //     })
        // });

    }

}
