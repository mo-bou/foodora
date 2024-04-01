import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['link', 'container', 'card'];

    connect() {
        this.getLinkTargets().forEach((target) => {
            target.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                this.getCardTargets().forEach((cardTarget) => {
                    cardTarget.classList.remove('active');
                });

                target.closest('.mercurial-card').classList.add('active');

                this.loadImports(target.getAttribute('href'));
            });
        });
    }

    async loadImports(href) {
        const response = await fetch(href);
        this.getContainerTarget().innerHTML = await response.text();
    }

    getLinkTargets() {
        return this.linkTargets;
    }

    getCardTargets() {
        return this.cardTargets;
    }

    getContainerTarget() {
        return this.containerTarget;
    }

}
