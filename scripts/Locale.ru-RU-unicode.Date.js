/*
---

name: Locale.ru-RU-unicode.Date

description: Date messages for Russian (utf-8).

license: MIT-style license

authors:
  - Evstigneev Pavel
  - Kuryanovich Egor

requires:
  - Locale

provides: [Locale.ru-RU.Date]

...
*/

(function(){

// Russian language pluralization rules, taken from CLDR project, http://unicode.org/cldr/
// one -> n mod 10 is 1 and n mod 100 is not 11;
// few -> n mod 10 in 2..4 and n mod 100 not in 12..14;
// many -> n mod 10 is 0 or n mod 10 in 5..9 or n mod 100 in 11..14;
// other -> everything else (example 3.14)
var pluralize = function (n, one, few, many, other){
	var modulo10 = n % 10,
		modulo100 = n % 100;

	if (modulo10 == 1 && modulo100 != 11){
		return one;
	} else if ((modulo10 == 2 || modulo10 == 3 || modulo10 == 4) && !(modulo100 == 12 || modulo100 == 13 || modulo100 == 14)){
		return few;
	} else if (modulo10 == 0 || (modulo10 == 5 || modulo10 == 6 || modulo10 == 7 || modulo10 == 8 || modulo10 == 9) || (modulo100 == 11 || modulo100 == 12 || modulo100 == 13 || modulo100 == 14)){
		return many;
	} else {
		return other;
	}
};

Locale.define('ru-RU', 'Date', {

	months: ['январь', '‘евраль', 'ћарт', 'јпрель', 'ћай', '»юнь', '»юль', 'јвгуст', '—ент€брь', 'ќкт€брь', 'Ќо€брь', 'ƒекабрь'],
	months_abbr: ['€нв', 'февр', 'март', 'апр', 'май','июнь','июль','авг','сент','окт','но€б','дек'],
	days: ['¬оскресенье', 'ѕонедельник', '¬торник', '—реда', '„етверг', 'ѕ€тница', '—уббота'],
	days_abbr: ['¬с', 'ѕн', '¬т', '—р', '„т', 'ѕт', '—б'],

	// Culture's date order: DD.MM.YYYY
	dateOrder: ['date', 'month', 'year'],
	shortDate: '%d.%m.%Y',
	shortTime: '%H:%M',
	AM: 'AM',
	PM: 'PM',
	firstDayOfWeek: 1,

	// Date.Extras
	ordinal: '',

	lessThanMinuteAgo: 'меньше минуты назад',
	minuteAgo: 'минуту назад',
	minutesAgo: function(delta){ return '{delta} ' + pluralize(delta, 'минуту', 'минуты', 'минут') + ' назад'; },
	hourAgo: 'час назад',
	hoursAgo: function(delta){ return '{delta} ' + pluralize(delta, 'час', 'часа', 'часов') + ' назад'; },
	dayAgo: 'вчера',
	daysAgo: function(delta){ return '{delta} ' + pluralize(delta, 'день', 'дн€', 'дней') + ' назад'; },
	weekAgo: 'неделю назад',
	weeksAgo: function(delta){ return '{delta} ' + pluralize(delta, 'недел€', 'недели', 'недель') + ' назад'; },
	monthAgo: 'мес€ц назад',
	monthsAgo: function(delta){ return '{delta} ' + pluralize(delta, 'мес€ц', 'мес€ца', 'мес€цев') + ' назад'; },
	yearAgo: 'год назад',
	yearsAgo: function(delta){ return '{delta} ' + pluralize(delta, 'год', 'года', 'лет') + ' назад'; },

	lessThanMinuteUntil: 'меньше чем через минуту',
	minuteUntil: 'через минуту',
	minutesUntil: function(delta){ return 'через {delta} ' + pluralize(delta, 'минуту', 'минуты', 'минут') + ''; },
	hourUntil: 'через час',
	hoursUntil: function(delta){ return 'через {delta} ' + pluralize(delta, 'час', 'часа', 'часов') + ''; },
	dayUntil: 'завтра',
	daysUntil: function(delta){ return 'через {delta} ' + pluralize(delta, 'день', 'дн€', 'дней') + ''; },
	weekUntil: 'через неделю',
	weeksUntil: function(delta){ return 'через {delta} ' + pluralize(delta, 'неделю', 'недели', 'недель') + ''; },
	monthUntil: 'через мес€ц',
	monthsUntil: function(delta){ return 'через {delta} ' + pluralize(delta, 'мес€ц', 'мес€ца', 'мес€цев') + ''; },
	yearUntil: 'через',
	yearsUntil: function(delta){ return 'через {delta} ' + pluralize(delta, 'год', 'года', 'лет') + ''; }

});



})();