defmodule PhoenixApiWeb.Plugs.RateLimit do
  @moduledoc """
  Phoenix Plug that enforces rate limits via PhoenixApi.RateLimiter.
  Must run AFTER Authenticate (needs conn.assigns.current_user).
  """
  import Plug.Conn
  import Phoenix.Controller

  def init(opts), do: opts

  def call(conn, _opts) do
    user_id = conn.assigns.current_user.id

    case PhoenixApi.RateLimiter.check(user_id) do
      :ok ->
        conn

      {:error, :rate_limited} ->
        conn
        |> put_status(:too_many_requests)
        |> put_view(json: PhoenixApiWeb.ErrorJSON)
        |> render(:"429")
        |> halt()
    end
  end
end
