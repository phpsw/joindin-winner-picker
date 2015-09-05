Joind.in Winner Picker
======================

A little tool for selecting a random [Joind.in](http://joind.in) event or talk comment from a
range of events by hashtag, loosely based on [@asgrim](https://github.com/asgrim)'s
[joindin-random-feedback-selector](https://github.com/asgrim/joindin-random-feedback-selector).

[PHPSW](http://phpsw.uk) use this to select winners of their monthly raffle
which attendees can enter by giving feedback on their last event or the latest
set of videos of the website (i.e. the last 2 events).

## Requirements

 - PHP 5.4+
 - Composer

## Installation

```sh
git clone git@github.com:phpsw/joindin-winner-picker.git
composer install
```

## Usage

```sh
./pick.php <tag> [<start date>] [<end date>]
```

## Example

In September 2015, we ran our first raffle and let our attendees feedback
on any of our previous events to enter:

```sh
./pick.php phpsw 2015-01-01

Joind.in Winner Picker!
Selecting from #phpsw events between 2015-01-01 and 2015-08-31

PHPSW: Testing, August 2015
  - ...comments
PHPSW: eCommerce, July 2015
  - ...comments
PHPSW: Coding Practices, June 2015
  - ...comments
PHPSW: Frameworks, May 2015
  - ...comments
PHPSW: Lightning Talks, April 2015
  - ...comments

And the winner is...
 - Roy - Great event, interesting talks and good atmosphere, my first time in attendance at PHPSW and will not be my last.
 - http://api.joind.in/v2.1/event_comments/1650
```

[![elePHPant](https://pbs.twimg.com/media/COKSEXMWUAE5hqD.jpg)](https://twitter.com/phpsw/status/640235009199198208)

[Roy won an elePHPant](https://twitter.com/phpsw/status/640235009199198208) :)

## Notes

- Uses events between the `first day of last month` and `last day of this month` by default
- Includes both event and talk comments
- Excludes hosts, hosts can't win :(
