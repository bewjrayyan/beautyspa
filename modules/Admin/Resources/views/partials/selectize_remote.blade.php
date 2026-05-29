@push('globals')
    <script>
        AestheticCart.selectize.push({
            load: function (query, callback) {
                var url = this.$input.data('url');

                if (url === undefined || query.length === 0) {
                    return callback();
                }

                axios
                    .get(url, {
                        params: {
                            query,
                        },
                    })
                    .then((response) => {
                        callback(response.data);
                    })
                    .catch(() => {
                        callback();
                    });
            }
        });
    </script>
@endpush
