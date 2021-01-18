@extends('layouts.app')

@section('content')

  <b-button
    v-if="!user.id"
    variant="link"
    @click.prevent="showAuthModal('login')"
  >
    <i class="fa fa-sign-in-alt"></i>
  </b-button>
  <b-button
    v-if="user.id"
    variant="link"
    @click.prevent="logout()"
  >
    <i class="fa fa-sign-out-alt"></i>
  </b-button>

  <b-container>
    <b-row>
      <b-col>
        <b-card>
          <p>
            This button has been pressed <span v-text="i"></span> times
            since the server started.
          </p>
          <b-button
            variant="primary"
            @click.prevent="increment()"
          >
            Press Me
          </b-button>
        </b-card>
      </b-col>
    </b-row>
  </b-container>

@endsection