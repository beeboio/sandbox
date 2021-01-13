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
@endsection