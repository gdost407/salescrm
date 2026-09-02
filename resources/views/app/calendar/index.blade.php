@extends('layouts.app')
@section('content')
<style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            color: #3d4b5c;
        }

        /* =========================
           MAIN CARD
        ========================= */

        .calendar-card {
            width: 100%;
            height: 835px;
            border: 0;
            border-radius: 0;
            overflow: hidden;
            box-shadow: none;
        }

        .calendar-wrapper {
            height: 100%;
            display: flex;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .calendar-sidebar {
            width: 371px;
            min-width: 371px;
            height: 100%;
            border-right: 1px solid #e1e4e8;
            background: #fff;
        }

        .add-event-wrapper {
            height: 106px;
            padding: 28px 29px;
            border-bottom: 1px solid #e1e4e8;
        }

        .btn-add-event {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 8px;

            background: linear-gradient(
                90deg,
                #6663ff 0%,
                #7674ff 100%
            );

            color: #fff;
            font-size: 18px;
            font-weight: 500;

            box-shadow: 0 5px 13px rgba(93, 91, 255, 0.28);
        }

        .btn-add-event:hover {
            color: #fff;
        }

        .btn-add-event i {
            font-size: 18px;
            margin-right: 10px;
        }

        /* =========================
           MINI CALENDAR
        ========================= */

        .mini-calendar-wrapper {
            height: 430px;
            padding: 27px 27px 20px;
            border-bottom: 1px solid #e1e4e8;
        }

        .mini-calendar-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .mini-nav-btn {
            width: 37px;
            height: 38px;
            border: 0;
            border-radius: 7px;
            background: #f0f1f3;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #6c7784;
            font-size: 20px;
        }

        .mini-month-name {
            font-size: 18px;
            font-weight: 400;
            color: #455364;
        }

        .mini-calendar {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            table-layout: fixed;
        }

        .mini-calendar th {
            height: 27px;
            text-align: center;
            font-size: 14px;
            font-weight: 400;
            color: #445162;
        }

        .mini-calendar td {
            height: 31px;
            padding: 0;
            text-align: center;
            font-size: 16px;
            color: #3f4d5c;
        }

        .mini-calendar td.muted {
            color: #aeb4bc;
        }

        .mini-calendar td.selected span {
            width: 45px;
            height: 45px;
            margin: -7px auto 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;
            background: #e9e8ff;
            color: #6563ff;
        }

        /* =========================
           FILTERS
        ========================= */

        .event-filters {
            padding: 34px 27px;
        }

        .event-filters h4 {
            margin: 0 0 23px;

            font-size: 22px;
            font-weight: 500;
            color: #3e4d5c;
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 11px;

            margin-bottom: 19px;

            font-size: 18px;
            color: #465466;
        }

        .filter-check {
            width: 23px;
            height: 23px;

            border-radius: 5px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #fff;
            font-size: 14px;
            font-weight: bold;
        }

        .filter-all {
            background: #8b98a8;
        }

        .filter-personal {
            background: #ff4329;
        }

        .filter-business {
            background: #6563ff;
        }

        .filter-family {
            background: #ffa800;
        }

        /* =========================
           MAIN CALENDAR
        ========================= */

        .calendar-main {
            flex: 1;
            min-width: 0;
            height: 100%;
            background: #fff;
        }

        /* =========================
           CALENDAR HEADER
        ========================= */

        .calendar-header {
            height: 105px;
            border-bottom: 1px solid #e1e4e8;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 31px 0 43px;
        }

        .calendar-header-left {
            display: flex;
            align-items: center;
            gap: 27px;
        }

        .calendar-arrow {
            border: 0;
            background: transparent;

            width: 27px;
            height: 35px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #65717e;
            font-size: 27px;
            padding: 0;
        }

        .calendar-month-title {
            margin: 0;

            font-size: 32px;
            line-height: 1;
            font-weight: 400;

            color: #64707c;
        }

        /* =========================
           VIEW BUTTONS
        ========================= */

        .calendar-view-buttons {
            display: flex;
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-view-buttons .btn {
            height: 47px;
            min-width: 81px;

            border: 0;
            border-right: 1px solid #d8d8fa;
            border-radius: 0;

            background: #e9e9ff;
            color: #7776ff;

            font-size: 18px;
            font-weight: 400;
        }

        .calendar-view-buttons .btn:last-child {
            border-right: 0;
        }

        .calendar-view-buttons .btn.active {
            background: linear-gradient(
                90deg,
                #6866ff,
                #716fff
            );
            color: #fff;
        }

        /* =========================
           CALENDAR GRID
        ========================= */

        .calendar-grid {
            height: calc(100% - 105px);
            display: grid;

            grid-template-columns: repeat(7, 1fr);
            grid-template-rows: 48px repeat(5, 1fr);

            border-left: 0;
        }

        /* Week header */

        .weekday {
            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-right: 1px solid #e0e3e7;
            border-bottom: 1px solid #e0e3e7;

            font-size: 18px;
            font-weight: 400;

            color: #344251;
        }

        .weekday:last-child {
            border-right: 0;
        }

        /* Calendar day */

        .calendar-day {
            position: relative;

            min-width: 0;
            min-height: 0;

            border-right: 1px solid #e0e3e7;
            border-bottom: 1px solid #e0e3e7;

            background: #fff;
            overflow: hidden;
        }

        .calendar-day:nth-child(7n) {
            border-right: 0;
        }

        .day-number {
            position: absolute;

            top: 11px;
            left: 10px;

            font-size: 18px;
            font-weight: 400;

            color: #66727f;
        }

        .day-number.muted {
            color: #aeb4bb;
        }

        .today-cell {
            background: #f1f2f3;
        }

        /* =========================
           EVENTS
        ========================= */

        .event {
            position: absolute;

            height: 36px;

            display: flex;
            align-items: center;

            padding: 0 10px;

            border-radius: 6px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

            font-size: 16px;
            font-weight: 400;
        }

        .event-purple {
            background: #e5e4ff;
            color: #6664ff;
        }

        .event-orange {
            background: #fff0d2;
            color: #ffa700;
        }

        .event-cyan {
            background: #d8f4fb;
            color: #05afd0;
        }

        .event-red {
            background: #ffe0dc;
            color: #ff3f2a;
        }

        .event-green {
            background: #def8d9;
            color: #50c72d;
        }

        /* Event positions */

        .event-design {
            top: 48px;
            left: 11px;
            right: 11px;
        }

        .event-dinner {
            top: 49px;
            left: 10px;
            right: calc(100% - 142px);
        }

        .event-dart {
            top: 93px;
            left: 10px;
            right: calc(100% - 142px);
        }

        .event-doctor {
            top: 49px;
            left: 10px;
            right: 11px;
        }

        .event-meeting {
            top: 93px;
            left: 10px;
            right: 11px;
        }

        .event-family {
            top: 49px;
            left: 10px;
            width: calc(200% - 21px);
        }

        .event-monthly {
            top: 49px;
            left: 10px;
            right: calc(100% - 142px);
        }

        .more-events {
            position: absolute;
            top: 138px;
            left: 12px;

            font-size: 16px;
            color: #65717f;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .calendar-sidebar {
                width: 300px;
                min-width: 300px;
            }

            .calendar-month-title {
                font-size: 26px;
            }

            .calendar-header {
                padding-left: 25px;
                padding-right: 20px;
            }

            .calendar-view-buttons .btn {
                min-width: 65px;
                font-size: 15px;
            }

            .weekday,
            .day-number {
                font-size: 14px;
            }

            .event {
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {

            .calendar-wrapper {
                display: block;
            }

            .calendar-sidebar {
                width: 100%;
                height: auto;
            }

            .mini-calendar-wrapper {
                height: auto;
            }

            .calendar-main {
                height: 700px;
            }

            .calendar-header {
                height: auto;
                min-height: 90px;
                flex-wrap: wrap;
                gap: 15px;
                padding: 20px;
            }

            .calendar-grid {
                overflow-x: auto;
                min-width: 850px;
            }
        }
    </style>
<div class="container-xxl flex-grow-1 container-p-y calendar-page">
  <div class="card calendar-card">

        <div class="calendar-wrapper">

            <!-- =========================================
                 LEFT SIDEBAR
            ========================================== -->

            <aside class="calendar-sidebar">

                <!-- Add Event -->
                <div class="add-event-wrapper">

                    <button class="btn btn-add-event">
                        <i class="bi bi-plus-lg"></i>
                        Add Event
                    </button>

                </div>


                <!-- Mini Calendar -->
                <div class="mini-calendar-wrapper">

                    <div class="mini-calendar-title">

                        <button class="mini-nav-btn">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <span class="mini-month-name">
                            September&nbsp; 2026
                        </span>

                        <button class="mini-nav-btn">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                    </div>


                    <table class="mini-calendar">

                        <thead>
                            <tr>
                                <th>Sun</th>
                                <th>Mon</th>
                                <th>Tue</th>
                                <th>Wed</th>
                                <th>Thu</th>
                                <th>Fri</th>
                                <th>Sat</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr>
                                <td class="muted">30</td>
                                <td class="muted">31</td>
                                <td>1</td>
                                <td class="selected">
                                    <span>2</span>
                                </td>
                                <td>3</td>
                                <td>4</td>
                                <td>5</td>
                            </tr>

                            <tr>
                                <td>6</td>
                                <td>7</td>
                                <td>8</td>
                                <td>9</td>
                                <td>10</td>
                                <td>11</td>
                                <td>12</td>
                            </tr>

                            <tr>
                                <td>13</td>
                                <td>14</td>
                                <td>15</td>
                                <td>16</td>
                                <td>17</td>
                                <td>18</td>
                                <td>19</td>
                            </tr>

                            <tr>
                                <td>20</td>
                                <td>21</td>
                                <td>22</td>
                                <td>23</td>
                                <td>24</td>
                                <td>25</td>
                                <td>26</td>
                            </tr>

                            <tr>
                                <td>27</td>
                                <td>28</td>
                                <td>29</td>
                                <td>30</td>
                                <td class="muted">1</td>
                                <td class="muted">2</td>
                                <td class="muted">3</td>
                            </tr>

                            <tr>
                                <td class="muted">4</td>
                                <td class="muted">5</td>
                                <td class="muted">6</td>
                                <td class="muted">7</td>
                                <td class="muted">8</td>
                                <td class="muted">9</td>
                                <td class="muted">10</td>
                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- Event Filters -->
                <div class="event-filters">

                    <h4>Event Filters</h4>

                    <div class="filter-item">
                        <span class="filter-check filter-all">
                            <i class="bi bi-check-lg"></i>
                        </span>
                        <span>View All</span>
                    </div>

                    <div class="filter-item">
                        <span class="filter-check filter-personal">
                            <i class="bi bi-check-lg"></i>
                        </span>
                        <span>Personal</span>
                    </div>

                    <div class="filter-item">
                        <span class="filter-check filter-business">
                            <i class="bi bi-check-lg"></i>
                        </span>
                        <span>Business</span>
                    </div>

                    <div class="filter-item">
                        <span class="filter-check filter-family">
                            <i class="bi bi-check-lg"></i>
                        </span>
                        <span>Family</span>
                    </div>

                </div>

            </aside>


            <!-- =========================================
                 RIGHT MAIN CALENDAR
            ========================================== -->

            <main class="calendar-main">

                <!-- Calendar Header -->

                <div class="calendar-header">

                    <div class="calendar-header-left">

                        <button class="calendar-arrow">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <button class="calendar-arrow">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                        <h1 class="calendar-month-title">
                            September 2026
                        </h1>

                    </div>


                    <!-- View Buttons -->

                    <div class="calendar-view-buttons">

                        <button class="btn active">
                            Month
                        </button>

                        <button class="btn">
                            Week
                        </button>

                        <button class="btn">
                            Day
                        </button>

                        <button class="btn">
                            List
                        </button>

                    </div>

                </div>


                <!-- =========================================
                     CALENDAR GRID
                ========================================== -->

                <div class="calendar-grid">

                    <!-- Weekdays -->

                    <div class="weekday">Sun</div>
                    <div class="weekday">Mon</div>
                    <div class="weekday">Tue</div>
                    <div class="weekday">Wed</div>
                    <div class="weekday">Thu</div>
                    <div class="weekday">Fri</div>
                    <div class="weekday">Sat</div>


                    <!-- ROW 1 -->

                    <div class="calendar-day">
                        <span class="day-number muted">30</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number muted">31</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">1</span>
                    </div>

                    <div class="calendar-day today-cell">

                        <span class="day-number">2</span>

                        <div class="event event-purple event-design">
                            11:02p Design Review
                        </div>

                    </div>

                    <div class="calendar-day">
                        <span class="day-number">3</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">4</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">5</span>
                    </div>


                    <!-- ROW 2 -->

                    <div class="calendar-day">
                        <span class="day-number">6</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">7</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">8</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">9</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">10</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">11</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">12</span>
                    </div>


                    <!-- ROW 3 -->

                    <div class="calendar-day">
                        <span class="day-number">13</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">14</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">15</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">16</span>
                    </div>

                    <div class="calendar-day">

                        <span class="day-number">17</span>

                        <div class="event event-orange event-dinner">
                            12a Dinner
                        </div>

                        <div class="event event-cyan event-dart">
                            Dart Game?
                        </div>

                        <span class="more-events">
                            +2 more
                        </span>

                    </div>

                    <div class="calendar-day">
                        <span class="day-number">18</span>
                    </div>

                    <div class="calendar-day">

                        <span class="day-number">19</span>

                        <div class="event event-red event-doctor">
                            12a Doctor's App
                        </div>

                        <div class="event event-purple event-meeting">
                            Meeting With Client
                        </div>

                    </div>


                    <!-- ROW 4 -->

                    <div class="calendar-day">
                        <span class="day-number">20</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">21</span>

                        <div class="event event-green event-family">
                            Family Trip
                        </div>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">22</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">23</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">24</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">25</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">26</span>
                    </div>


                    <!-- ROW 5 -->

                    <div class="calendar-day">
                        <span class="day-number">27</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">28</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">29</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number">30</span>
                    </div>

                    <div class="calendar-day">

                        <span class="day-number muted">1</span>

                        <div class="event event-purple event-monthly">
                            Monthly Meeting
                        </div>

                    </div>

                    <div class="calendar-day">
                        <span class="day-number muted">2</span>
                    </div>

                    <div class="calendar-day">
                        <span class="day-number muted">3</span>
                    </div>

                </div>

            </main>

        </div>

    </div>
</div>
@endsection