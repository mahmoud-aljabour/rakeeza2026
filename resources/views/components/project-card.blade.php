@php
    $projectImages = $project->imageUrls();
    $imageCount = count($projectImages);
@endphp
<article
    class="project-card"
    data-lightbox="{{ json_encode($project->lightboxPayload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
>
    <img src="{{ $project->imageUrl() }}" alt="{{ $project->title }}" loading="lazy" decoding="async" data-project-image>
    @if ($imageCount > 1)
        <span class="project-photo-count">{{ $imageCount }} صور</span>
    @endif
    <div class="project-caption">
        <strong>{{ $project->title }}</strong>
        @if ($project->details)
            <p>{{ $project->details }}</p>
        @endif
    </div>
    <button
        type="button"
        class="project-card-open"
        data-lightbox-open
        data-lightbox-index="0"
        aria-label="عرض صور {{ $project->title }}"
    ></button>
</article>
