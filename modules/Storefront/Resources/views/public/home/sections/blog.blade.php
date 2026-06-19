<section x-data="Blog" class="blog-posts-wrap">
    <div class="container">
        <div class="blog-posts-inner">
            <div class="blog-posts-header">
                <h3 class="section-title">{{ $blog['title'] }}</h3>
    
                <a href="{{ route('blog_posts.index') }}" class="view-all">
                    {{ trans("storefront::blog.blog_posts.view_all") }}
                </a>
            </div>
    
            <div class="blog-posts swiper" x-ref="blogSlider">
                <div class="swiper-wrapper">
                    @foreach($blog['blogPosts'] as $blogPost)
                        <div class="swiper-slide">
                            @include('storefront::public.partials.blog_post_card', ['blogPost' => $blogPost])
                        </div>
                    @endforeach
                </div>
            </div>

            @include('storefront::public.partials.product_slider_controls')
        </div>
    </div>
</section>
