@php
    $projectImages = $project->imageUrls();
    $imageCount = count($projectImages);
    $title = $project->displayTitle();
@endphp
<article
    class="project-card"
    data-lightbox="{{ json_encode($project->lightboxPayload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
>
    <img src="{{ $project->imageUrl() }}" alt="{{ $title }}" loading="lazy" decoding="async" data-project-image>
    @if ($imageCount > 1)
        <span class="project-photo-count">{{ trans_choice('site.photos', $imageCount, ['count' => $imageCount]) }}</span>
    @endif
    <div class="project-caption">
        <strong>{{ $title }}</strong>
        @if ($project->details)
            <p>{{ $project->displayDetails() }}</p>
        @endif
    </div>
    <button
        type="button"
        class="project-card-open"
        data-lightbox-open
        data-lightbox-index="0"
        aria-label="{{ __('site.lightbox.view_photos', ['title' => $title]) }}"
    ></button>
</article>
