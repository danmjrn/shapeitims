// select elements
const video1 = document.getElementById('vid_1');
const toggle1 = document.getElementById('vid_1_toggle');
const progress1 = document.getElementById('vid_1_progress');
const progressBar1 = document.getElementById('vid_1_progress__filled');
const fullscreen1 = document.getElementById('vid_1_fullscreen');
const audioControl1 = document.getElementById('vid_1_audio_control');
const cardDetails1 = document.getElementById('card-details_vid_1');

function pauseVideoItem (videoItem, toggleItem, audioControlItem) {
    videoItem.pause();

    toggleItem.innerHTML = `<span class="material-icons" style="font-size: 16px">play_arrow</span>`;

    $(videoItem).prop('muted', true);

    $(audioControlItem).html
        (
            `
<span class="material-icons" style="font-size: 16px">volume_off</span>
<span style="margin-left: 5px; color: #FFF; display: block; font-size: 10px">Tap to unmute</span>
            `
        );
}

/*
* video 1
* */
// add event listeners
// Play or Pause events(On video click)
if (video1) {
    video1.addEventListener
        (
            'click',
            function () {
                // toggle for video play and pause
                const playOrPause = video1.paused ? 'play' : 'pause';

                video1[playOrPause]();

                // toggle for icon change when play or pause
                if (playOrPause === 'play') {
                    toggle1.innerHTML = `<span class="material-icons" style="font-size: 16px">pause</span>`;
                }
                else {
                    toggle1.innerHTML = `<span class="material-icons" style="font-size: 16px">play_arrow</span>`;
                }
            }
        );

    // (On button click)
    toggle1.addEventListener
        (
            'click',
            function () {
                // toggle for video play and pause
                const playOrPause = video1.paused ? 'play' : 'pause';

                video1[playOrPause]();

                // toggle for icon change when play or pause
                if (playOrPause === 'play') {
                    toggle1.innerHTML = `<span class="material-icons" style="font-size: 16px">pause</span>`;
                }
                else {
                    toggle1.innerHTML = `<span class="material-icons" style="font-size: 16px">play_arrow</span>`;
                }
            }
        );

    // Change progress wrt time
    video1.addEventListener
        (
            'timeupdate',
            function () {
                // convert video's current time into percentage
                const percent = (video1.currentTime / video1.duration) * 100;
                // append it to the flexBasis property (CSS)
                // console.log(percent);
            }
        );

    // add full screen event
    fullscreen1.addEventListener
        (
            'click',
            function () {
                video1.requestFullscreen().then(r => {console.log(r)});
            }
        );

    video1.addEventListener
        (
            "canplay",
            function () {
                video1.play();

                toggle1.innerHTML = `<span class="material-icons" style="font-size: 16px">pause</span>`;
            }
        );

    $(video1).on
        (
            'ended',
            function () {
                pauseVideoItem(video1, toggle1, audioControl1);


            }
        );

    $(video1).prop('muted', true);

    $(audioControl1).click
        (
            function () {
                if ($(video1).prop('muted')) {
                    $(video1).prop('muted', false);

                    $(this).html
                        (
                            `
<span class="material-icons" style="font-size: 16px">volume_up</span>
<span style="margin-left: 5px; color: #FFF; display: block; font-size: 10px">Tap to mute</span>
                            `
                        );
                }
                else {
                    $(video1).prop('muted', true);

                    $(this).html
                        (
                            `
<span class="material-icons" style="font-size: 16px">volume_off</span>
<span style="margin-left: 5px; color: #FFF; display: block; font-size: 10px">Tap to unmute</span>
                            `
                        );
                }
            }
        );

    $(video1).hover
        (
            () => {
                $(video1).css("z-index", "0");
            },
            () => {
                $(video1).css("z-index", "2");
            }
        );
}