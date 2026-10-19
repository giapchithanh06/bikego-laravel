{{-- List of reviews: Full name - Rating - Comment --}}
@if ($reviews->isEmpty())
  <p class="empty-note">Chưa có đánh giá nào.</p>
@else
  <ul class="review-list">
    @foreach ($reviews as $review)
      <li>
        <strong>{{ optional($review->user)->name ?? 'Ẩn danh' }}</strong>
        - @include('partials.stars', ['value' => $review->rating]) {{ $review->rating }}/5
        - {{ $review->comment ?: '(không có bình luận)' }}
        <small class="muted">{{ optional($review->created_at)->format('d/m/Y H:i') }}</small>
      </li>
    @endforeach
  </ul>
@endif
