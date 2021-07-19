$(document).ready(function () {
    $('.ac-mask-int').mask('A', {translation: {'A': {pattern: /[0-9]/, recursive: true}}});
    $('.ac-mask-float').mask('A', {translation: {'A': {pattern: /[0-9.,]/, recursive: true}}});
    $('.ac-mask-email').mask('A', {translation: {'A': {pattern: /[@a-zA-Z0-9_.-]/, recursive: true}}, placeholder: 'example@domain.ru'});
    $('.ac-mask-phone').mask('0 (000) 000-00-00', {placeholder: '- (---) --- -- --'});
    $('.ac-mask-hex').mask('ABBBBBB', {translation: {'A': {pattern: /[#]/}, 'B': {pattern: /[a-zA-Z0-9]/}}, placeholder: '#000000'});
    $('.ac-mask-link').mask('A', {translation: {'A': {pattern: /[@a-zA-Z0-9_:/?&=#%.-]/, recursive: true}}, placeholder: 'https://example.ru'});
    $('.ac-mask-safe').mask('A', {translation: {'A': {pattern: /[a-zA-Z0-9_.-]/, recursive: true}}});
    $('.ac-mask-en').mask('A', {translation: {'A': {pattern: /[a-zA-Z0-9 ]/, recursive: true}}});
    $('.ac-mask-ru').mask('A', {translation: {'A': {pattern: /[а-яА-я0-9 ]/, recursive: true}}});
});
