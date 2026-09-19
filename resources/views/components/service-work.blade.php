@props(['project', 'index' => 1])

@php
    $projectImages = $project->imageUrls();
    $imageCount = count($projectImages);
    $title = $project->displayTitle();
    $lightbox = json_encode($project->lightboxPayload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $isRtl = \App\Support\AppLocale::isRtl();
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
                aria-label="{{ __('site.lightbox.view_photos', ['title' => $title]) }}"
            >
                <img src="{{ $project->imageUrl() }}" alt="{{ $title }}" loading="lazy" decoding="async" data-work-image>
                @if ($imageCount > 1)
                    <span class="project-photo-count" data-work-count>{{ trans_choice('site.photos', $imageCount, ['count' => $imageCount]) }}</span>
                @endif
            </button>
            @if ($imageCount > 1)
                <button type="button" class="service-work-nav is-prev" data-work-prev aria-label="{{ __('site.lightbox.prev') }}">
                    <i class="fa-solid {{ $isRtl ? 'fa-chevron-right' : 'fa-chevron-left' }}"></i>
                </button>
                <button type="button" class="service-work-nav is-next" data-work-next aria-label="{{ __('site.lightbox.next') }}">
                    <i class="fa-solid {{ $isRtl ? 'fa-chevron-left' : 'fa-chevron-right' }}"></i>
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
                        aria-label="{{ __('site.lightbox.view_photo_of', ['index' => $imageIndex + 1, 'title' => $title]) }}"
                    >
                        <img src="{{ $imageUrl }}" alt="" loading="lazy" decoding="async">
                    </button>
                @endforeach
            </div>
        @endif
    </div>
    <div class="service-work-body">
        <h3>{{ $title }}</h3>
        @if ($project->details)
            <p>{{ $project->displayDetails() }}</p>
        @endif
    </div>
</article>
