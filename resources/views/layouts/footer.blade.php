<footer class="bg-body-tertiary text-body-secondary mt-auto py-4 border-top">
    <div class="container text-center">
        <small class="version-wrapper">
            Sharemeister 
            <span class="version-tag" data-build="Build: {{ config('version.build') }}">
                v{{ config('version.tag') }}
            </span> 
            &bull;
            <a href="https://github.com/flymia/Sharemeister/">GitHub</a>
        </small>
    </div>
</footer>

<style>
    .version-tag {
        position: relative;
        cursor: help;
        color: var(--bs-primary);
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .version-tag::after {
        content: attr(data-build);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-5px);
        background: var(--bs-dark);
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, transform 0.2s ease;
        pointer-events: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    .version-tag:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-10px);
    }
</style>