<?php

const AUDIO_TILLY_AVAILABLE_VOICES = [
    'James' => 'James - AudioTilly.com Example text to speech voice.mp3',
    'Faith' => 'Faith - AudioTilly.com Example text to speech voice.wav',
    'Matt' => 'Matt - AudioTilly.com Example text to speech voice.wav',
    'Julie' => 'Julie - AudioTilly.com Example text to speech voice.mp3',
    'Pooja' => 'Pooja - AudioTilly.com Example text to speech voice.wav',
    'Vijay' => 'Vijay - AudioTilly.com Example text to speech voice.mp3',
    'Anil' => 'Anil - AudioTilly.com Example text to speech voice.wav',
    'Jules' => 'Jules - AudioTilly.com Example text to speech voice.mp3',
    'Ben' => 'Ben - AudioTilly.com Example text to speech voice.mp3',
    'Chris' => 'Chris - AudioTilly.com Example text to speech voice.mp3',
    'Isla' => 'Isla - AudioTilly.com Example text to speech voice.wav',
    'Jack' => 'Jack - AudioTilly.com Example text to speech voice.mp3',
    'Manon' => 'Manon - AudioTilly.com Example text to speech voice.mp3',
    'Jacque' => 'Jacque - AudioTilly.com Example text to speech voice.wav',
    'Lucy' => 'Lucy - AudioTilly.com Example text to speech voice.wav',
    'Martha' => 'Martha - AudioTilly.com Example text to speech voice.wav',
    'Ken' => 'Ken - AudioTilly.com Example text to speech voice.wav',
    'Albert' => 'Albert - AudioTilly.com Example text to speech voice.mp3',
    'Nisha' => 'Nisha - AudioTilly.com Example text to speech voice.mp3',
    'Mark' => 'Mark - AudioTilly.com Example text to speech voice.mp3',
    'Carl' => 'Carl - AudioTilly.com Example text to speech voice.mp3',
    'Mellisa' => 'Mellisa - AudioTilly.com Example text to speech voice.mp3',
    'Addison' => 'Addison - AudioTilly.com Example text to speech voice.mp3'
];

const AUDIO_TILLY_PLAYER_SKIN = 'audio_tilly_player_skin';
const AUDIO_TILLY_VOICES = 'audio_tilly_player_voices';

const LABEL_DEFAULT = 'Default';
const AUDIO_TILLY_GENDER_ID = 'audio_tilly_gender_id';
const AUDIO_TILLY_SOURCE_LANGUAGE = 'audio_tilly_source_language';
const AUDIO_TILLY_LICENSE_KEY = 'audio_tilly_license_key';
const AUDIO_TILLY_LICENSE_ID = 'audio_tilly_license_id';
const AUDIO_TILLY_CONVERSIONS_LEFT = 'audio_tilly_conversions_left';
const AUDIO_TILLY_FIRST_SAVE = 'audio_tilly_first_save';

const AUDIO_TILLY_LISTEN_TIMEOUT = 30;

const AUDIO_TILLY_SERVICE = AUDIOTILLY_API_HOST;
const AUDIO_TILLY_WP_SERVICE = AUDIO_TILLY_SERVICE . '/plugin';
const AUDIO_TILLY_IS_ENOUGH_CREDITS_URL = AUDIO_TILLY_WP_SERVICE . '/credits/authorization';
const AUDIO_TILLY_KEYS_URL = AUDIO_TILLY_WP_SERVICE . '/get-key'; // signup
const AUDIO_TILLY_UPDATE_PLUGIN_DETAILS_URL = AUDIO_TILLY_WP_SERVICE . '/get-key'; // update_plugin_details
const AUDIO_TILLY_LANGUAGES = AUDIO_TILLY_WP_SERVICE . '/languages?installkey=';

const AUDIOTILLY = 'audiotilly';
const AUDIO_TILLY = 'audio_tilly';

const AUDIO_TILLY_SAVED_TITLE = 'audio_tilly_saved_title';
const AUDIO_TILLY_SAVED_BODY = 'audio_tilly_saved_body';
const AUDIO_TILLY_SAVED_EXCERPT = 'audio_tilly_saved_excerpt';
