import ImagePicker from './ImagePicker';
import MediaPicker from './MediaPicker';
import MediaGrid from './MediaGrid';
import Uploader from './Uploader';

window.MediaPicker = MediaPicker;
window.MediaGrid = MediaGrid;

if ($('.image-picker').length !== 0) {
    new ImagePicker();
}

if ($('.dropzone').length !== 0) {
    new Uploader();
}

if ($('#media-grid').length !== 0) {
    const $grid = $('#media-grid');

    new MediaGrid('#media-grid', {
        type: $grid.data('type') || null,
        pickerMode: Boolean($grid.data('pickerMode')),
    });
}
