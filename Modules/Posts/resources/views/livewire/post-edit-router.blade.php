<div>
    @if($post->post_type === 'gallery')
        <livewire:posts.post-edit-gallery :post="$post" :key="'gallery-'.$post->post_id" />
    @elseif($post->post_type === 'video')
        <livewire:posts.post-edit-video :post="$post" :key="'video-'.$post->post_id" />
    @else
        <livewire:posts.post-edit-news :post="$post" :key="'news-'.$post->post_id" />
    @endif
</div>

