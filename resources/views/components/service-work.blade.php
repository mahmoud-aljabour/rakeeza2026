@props(['project', 'index' => 1])

@php
    $projectImages = $project->imageUrls();
    $imageCount = count($projectImages);
    $lightbox = json_encode($project->lightboxPayload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
<article class="service-work" data-lightbox="{{ $lightbox }}">
    <div class="service-work-media">
        <span class="service-work-index">{{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}</span>
        <div class="service-work-stage">
            <button
                type="button"
                class="service-work-main"
                data-lightbox-open
                data-lightbox-index="0"
                aria-label="عرض صور {{ $project->title }}"
            >
                <img src="{{ $project->imageUrl() }}" alt="{{ $project->title }}" loading="lazy" decoding="async" data-work-image>
                @if ($imageCount > 1)
                    <span class="project-photo-count" data-work-count>{{ $imageCount }} صور</span>
                @endif
            </button>
            @if ($imageCount > 1)
                <button type="button" class="service-work-nav is-prev" data-work-prev aria-label="الصورة السابقة">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <button type="button" class="service-work-nav is-next" data-work-next aria-label="الصورة التالية">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            @endif
        </div>
        @if ($imageCount > 1)
            <div class="service-work-thumbs">
                @foreach ($projectImages as $imageIndex => $imageUrl)
                    <button
                        type="button"
                        class="service-work-thumb{{ $imageIndex === 0 ? ' is-active' : '' }}"
                        data-lightbox-open
                        data-lightbox-index="{{ $imageIndex }}"
                        aria-label="عرض الصورة {{ $imageIndex + 1 }} من {{ $project->title }}"
                    >
                        <img src="{{ $imageUrl }}" alt="" loading="lazy" decoding="async">
                    </button>
                @endforeach
            </div>
        @endif
    </div>
    <div class="service-work-body">
        <h3>{{ $project->title }}</h3>
        @if ($project->details)
            <p>{{ $project->details }}</p>
        @endif
    </div>
</article>
