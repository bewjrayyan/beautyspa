Alpine.data("ProductRating", ({ rating_percent, reviews, reviews_count }) => ({
    ratingPercent: rating_percent,
    reviewCount: reviews_count ?? reviews?.length,

    get hasReviewCount() {
        return this.reviewCount !== undefined;
    },
}));
